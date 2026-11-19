'use strict';

function initializePlayerState(data) {
  data.signedIn ??= true;
  data.profile ??= { ...CURRENT_USER, sms: true };
  data.subscriptions ??= structuredClone(INITIAL_SUBSCRIPTIONS);
  data.notifications ??= structuredClone(INITIAL_NOTIFICATIONS);
  data.queues ??= {};
}

const demoConfig = document.querySelector('#courtbook-config').dataset;
const demoRoutes = JSON.parse(demoConfig.urls);

function url(target) {
  const [path, query = ''] = target.split('?');
  const [route, id] = path.split('/');
  const name = id ? (route === 'courts' ? 'court' : 'game') : route;
  const endpoint = new URL(id ? demoRoutes[name].replace('__id__', encodeURIComponent(id)) : demoRoutes[name]);
  for (const [key, value] of new URLSearchParams(query)) endpoint.searchParams.set(key, value);
  return endpoint.href;
}

function currentPage() {
  const page = document.body.dataset.page;
  if (page === 'court') return 'courts/' + demoConfig.id + location.search;
  if (page === 'game') return 'games/' + demoConfig.id + location.search;
  return page + location.search;
}

function assetUrl(path) {
  return new URL(path, demoConfig.assets).href;
}

const actionButton = (action, label, value = '', style = 'outline') =>
  '<button type="button" class="' + style + '" data-action="' + action + '" data-value="' + escape(value) + '">' + label + '</button>';
const field = (label, input) => '<label>' + label + input + '</label>';
const block = content => '<div class="panel stack">' + content + '</div>';
const cards = items => '<div class="player-grid">' + items.join('') + '</div>';
const section = (title, desc, body) => page(heading('CourtBook', title, desc) + body);
const formText = (name, value = '', type = 'text', attrs = '') =>
  '<input name="' + name + '" type="' + type + '" value="' + escape(value) + '" ' + attrs + ' required>';
const formSelect = (name, options) => '<select name="' + name + '">' + options + '</select>';
const formBox = (id, content, button) => '<form class="panel stack" id="' + id + '">' + content + '<button>' + button + '</button></form>';
const timeLine = (date, time, duration) => formatDate(date) + ' · ' + formatHour(time) + ' – ' + formatHour(time + duration);
const timeOptions = hour => hourOptions(6, 23, hour);

function requirePlayer() {
  if (state.signedIn) return true;
  showDialog('Login required', '<p>Log in to continue with your player account.</p>' + link('login', 'Log in') + ' ' + link('register', 'Create account', 'outline'));
  return false;
}

function myBookings(params) {
  const status = ['upcoming','completed','cancelled'].includes(params.get('status')) ? params.get('status') : 'upcoming';
  return section('My bookings', 'Manage your demo court reservations.', state.signedIn ?
    '<div class="row results">' + ['upcoming','completed','cancelled'].map(s => link('bookings?status=' + s, s[0].toUpperCase() + s.slice(1), s === status ? '' : 'outline')).join('') + link('my-games','My games','outline') + '</div>' +
    (state.bookings.some(b => b.status === status) ? cards(state.bookings.filter(b => b.status === status).map(b => {
      const court = getCourt(b.courtId);
      return block(tag(sportLabel(court.sport)) + '<h2>' + escape(court.facility) + '</h2><p>' + timeLine(b.date,b.startHour,b.duration) + '</p><p>' + escape(court.area) + ' · ' + jod(b.total) + ' · ' + escape(b.ref) + '</p><div class="row">' +
        actionButton('booking-info','Booking details',b.ref) + (status === 'upcoming' ? actionButton('cancel','Cancel booking',b.ref) : '') + '</div>');
    })) : empty('No ' + status + ' bookings.')) : link('login','Log in'));
}

function myGames(params) {
  const view = params.get('view') || 'organized';
  const tabs = [['organized','Organized by me'],['joined','Joined games'],['waiting','Waiting list'],['offers','Pending invitations']];
  const selected = state.games.filter(g => view === 'organized' ? g.organizerId === 'me' : view === 'joined' ? g.organizerId !== 'me' && g.playerIds.includes('me') : g.waitlistIds.includes('me'));
  return section('My games','Manage the games you organize, join or wait for.', state.signedIn ?
    '<div class="row results">' + tabs.map(([id,label]) => link('my-games?view=' + id,label,view === id ? '' : 'outline')).join('') + '</div>' +
    (view === 'offers' ? link('waitlist','Review invitations') : selected.length ? '<div class="grid">' + selected.map(gameCard).join('') + '</div>' : empty('No games in this view. ' + link('games','Find games'))) : link('login','Log in'));
}

function addAlert(kind,title,body,target) {
  state.notifications.unshift({ id: crypto.getRandomValues(new Uint32Array(1))[0].toString(36), kind,title,body,href:target,time:'Just now',read:false });
}

function notificationPage() {
  return section('Notifications','Review your booking, game and subscription updates.',
    state.signedIn ? actionButton('read-all','Mark all read') + '<div class="section player-grid">' + state.notifications.map(n =>
      block(tag(n.read ? n.time : 'Unread · ' + n.time) + '<h2>' + escape(n.title) + '</h2><p>' + escape(n.body) + '</p>' +
        (!n.read ? actionButton('read-alert','Mark read',n.id) : '') )).join('') + '</div>' : link('login','Log in'));
}

function profilePage() {
  const p = state.profile;
  const avatar = p.photo ? image(p.photo,'Profile photo','class="profile-avatar"') :
    '<span class="profile-avatar">' + escape(p.name.split(/\s+/).slice(0,2).map(n => n[0]).join('').toUpperCase()) + '</span>';
  return section('Profile & settings','Manage your player account and notifications.',state.signedIn ?
    block('<div class="row">' + avatar + '<div><h2>' + escape(p.name) + '</h2><p class="muted">' + escape(p.email) + ' · ' + escape(p.phone) + '</p></div></div>' +
      '<div class="row">' + p.preferredSports.map(s => tag(sportLabel(s))).join('') + tag(p.sms ? 'SMS preferences on' : 'SMS preferences off') + '</div><div class="row">' +
      actionButton('edit-profile','Edit profile') + actionButton('password','Change password') + link('bookings','My activity','outline') + actionButton('logout','Log out') + '</div>') : link('login','Log in'));
}

function hasSubscriptionSlot(courtId,date,hour) {
  return state.subscriptions.some(s => s.status === 'active' && s.courtId === courtId &&
    s.sessionDates?.includes(date) && hour >= s.startHour && hour < s.startHour + s.duration);
}

function quoteFields(form) {
  const data = new FormData(form);
  return { courtId:String(data.get('court')), days:data.getAll('day').map(Number),
    startHour:Number(data.get('hour')), duration:Number(data.get('duration')), startDate:validDate(data.get('date')) };
}

function scheduleQuote(draft) {
  const quote = quoteSubscription(draft);
  if (!quote) return null;
  const court = getCourt(draft.courtId);
  const conflicts = quote.sessions.filter(s => !s.available || !Array.from({length:draft.duration},(_,i) =>
    freeSlots(court,s.date).some(slot => slot.hour === draft.startHour + i)).every(Boolean));
  return { ...quote, conflicts };
}

function subscriptionsPage() {
  return section('Monthly court subscriptions','Build a recurring schedule and see the deterministic monthly quote.',
    '<div class="details">' + formBox('subscription-form',
      field('Sport',formSelect('sub-sport',sports())) + field('Facility and court',formSelect('court',COURTS.map(c=>option(c.id,c.facility + ' · ' + c.name)).join(''))) +
      '<fieldset><legend>Weekly sessions</legend>' + WEEKDAYS.map((day,i) => '<label><input type="checkbox" name="day" value="' + i + '">' + day + '</label>').join('') + '</fieldset>' +
      field('Start date',dateInput()) + field('Preferred time',formSelect('hour',timeOptions(18))) +
      field('Session duration',formSelect('duration',[1,2,3].map(i=>option(i,i+' hr')).join(''))) + tag('Billing period · one month'), 'Subscribe now') +
    '<aside id="subscription-quote" class="panel stack" aria-live="polite"></aside></div><div class="section">' + link('my-subscriptions','My subscriptions','outline') + '</div>');
}

function quoteMarkup(quote) {
  if (!quote || !quote.sessions.length) return '<h2>Monthly quote</h2><p>Choose at least one weekly session.</p>';
  return '<h2>' + jod(quote.total) + ' / month</h2><p>' + quote.scheduledCount + ' priced sessions × ' + jod(quote.pricePerSession) +
    '<br>Subtotal ' + jod(quote.subtotal) + '<br>Discount ' + Math.round(quote.discountRate*100) + '% · ' + jod(quote.discount) + '</p>' +
    (quote.conflicts.length ? '<p role="status">Change your schedule: ' + quote.conflicts.length + ' demo slots overlap or are unavailable.</p>' : '') +
    '<div class="tags">' + quote.sessions.map(s=>tag(formatDate(s.date) + (quote.conflicts.includes(s) ? ' · unavailable' : ''))).join('') + '</div>';
}

function updateQuote() {
  const form = document.querySelector('#subscription-form');
  if (form) document.querySelector('#subscription-quote').innerHTML = localizedMarkup(quoteMarkup(scheduleQuote(quoteFields(form))) +
    actionButton('pricing','Pricing breakdown'));
}

function mySubscriptions() {
  return section('My subscriptions','Review schedules, remaining sessions and renewal status.', state.signedIn ?
    cards(state.subscriptions.map(s => block(tag(s.status) + '<h2>' + escape(getCourt(s.courtId)?.facility) + '</h2><p>' + s.days.map(d=>WEEKDAYS[d]).join(', ') +
      ' · ' + formatHour(s.startHour) + ' · ' + s.duration + ' hr</p><p>' + jod(s.monthlyTotal) + ' / month · ' + Math.max(0,s.sessionsTotal-s.sessionsUsed) +
      ' sessions remaining</p><p>Renewal ' + (s.renews ? 'on' : 'off') + '</p>' +
      (s.sessionDates?.length ? '<div class="tags">' + s.sessionDates.slice(s.sessionsUsed).filter(d=>d>=today).map(d=>tag(formatDate(d))).join('') + '</div>' : '') +
      (s.status === 'active' ? actionButton('stop-sub','Cancel subscription',s.id) : '')))) + '<div class="section">' + link('subscriptions','Build a subscription') + '</div>' : link('login','Log in'));
}


let wizard;
let pendingSubscription;
let offerTimer;
const playerName = id => getPlayer(id)?.name || 'Player';

function createGamePage() {
  wizard ??= { step:0, sport:SPORTS[0].id, title:'', description:'', skill:'All Levels', court:COURTS[0].id,
    date:today, hour:'', duration:1, minimum:2, capacity:8, price:0, reservation:'' };
  const w = wizard;
  const courts = COURTS.filter(c=>c.sport===w.sport);
  if (!courts.some(c=>c.id===w.court)) w.court=courts[0].id;
  const court = getCourt(w.court);
  const bookings = state.bookings.filter(b=>b.status==='upcoming' && b.courtId===w.court &&
    !state.games.some(g=>g.bookingRef===b.ref && g.status!=='cancelled'));
  const reserved = bookings.find(b=>b.ref===w.reservation);
  const available = (reserved ? [reserved.startHour] : freeSlots(court,w.date).map(s=>s.hour)
    .filter(h => Array.from({length:Number(w.duration)},(_,i)=>h+i).every(t=>freeSlots(court,w.date).some(s=>s.hour===t))));
  const step = [
    field('Sport',formSelect('sport',SPORTS.map(s=>option(s.id,s.label,w.sport)).join(''))) +
    field('Game title',formText('title',w.title,'text','maxlength="80"')) +
    field('Description','<textarea name="description" maxlength="500" required>'+escape(w.description)+'</textarea>') +
    field('Skill level',formSelect('skill',['All Levels','Beginner','Intermediate','Advanced'].map(s=>option(s,s,w.skill)).join(''))),

    field('Court',formSelect('court',courts.map(c=>option(c.id,c.facility+' · '+c.name,w.court)).join(''))) +
    field('Existing demo reservation',formSelect('reservation',option('','Propose without reserving',w.reservation)+bookings.map(b=>option(b.ref,b.ref+' · '+formatDate(b.date),w.reservation)).join(''))) +
    (reserved ? '<p>'+timeLine(reserved.date,reserved.startHour,reserved.duration)+'</p>' :
      field('Date',dateInput(w.date)) +
      field('Duration',formSelect('duration',[1,2,3].map(i=>option(i,i+' hr',Number(w.duration))).join(''))) +
      field('Available start time',formSelect('hour',option('','Choose a time',w.hour)+available.map(h=>option(String(h),formatHour(h),String(w.hour))).join('')))),

    field('Minimum players',formText('minimum',w.minimum,'number','min="2" max="40"')) +
    field('Maximum players',formText('capacity',w.capacity,'number','min="2" max="40"')) +
    field('Cost per player (JOD)',formText('price',w.price,'number','min="0" max="9999" step=".01"')) + tag('Public game'),

    gameSummary(w)
  ];
  const headings=['Game details','Court & schedule','Players','Review & publish'];
  return section('Create a public game','Organize a pickup game with players in Amman.',state.signedIn ?
    '<div class="row results">'+headings.map((h,i)=>tag((i+1)+'. '+h)).join('')+'</div>'+
    formBox('game-wizard','<h2>'+headings[w.step]+'</h2>'+step[w.step]+(w.step? actionButton('wizard-back','Back') : ''),w.step===3?'Review and publish':'Continue') : link('login','Log in'));
}

function gameSummary(w) {
  return '<h3>'+escape(w.title)+'</h3><p>'+escape(getCourt(w.court).facility)+' · '+timeLine(w.date,Number(w.hour),Number(w.duration))+'</p><p>'+
    escape(w.minimum)+' needed · '+escape(w.capacity)+' maximum · '+jod(Number(w.price))+' per player · '+jod(getCourt(w.court).pricePerHour*Number(w.duration))+' estimated court cost</p>'+
    tag(w.reservation?'Existing demo booking':'Proposed · court not reserved');
}

function activeOfferCount(game) {
  return (state.queues[game.id]?.offers || []).filter(o=>o.status==='active').length;
}

function queueFor(game) {
  return state.queues[game.id] || createWaitlist(game.maxPlayers,game.playerIds,game.waitlistIds);
}

function applyQueue(game, queue) {
  const previous = state.queues[game.id];
  state.queues[game.id]=queue;
  game.playerIds=queue.confirmed;
  game.waitlistIds=queue.queue.filter(e=>['waiting','offered'].includes(e.status)).map(e=>e.userId);
  game.status=game.playerIds.length+activeOfferCount(game)>=game.maxPlayers ? 'full':'open';
  for (const offer of queue.offers.filter(e=>e.userId==='me')) {
    const old=previous?.offers.find(o=>o.id===offer.id);
    if (!old && offer.status==='active') addAlert('waitlist_spot','A spot opened up',game.title+' · 15 minutes to confirm.','waitlist');
    if (old?.status==='active' && offer.status==='expired') addAlert('invite_expired','Invitation expired',game.title,'waitlist');
  }
}

function updateGameMembership(game) {
  let queue=queueFor(game);
  if (game.playerIds.includes('me')) {
    if (game.organizerId==='me') { notify('Organizers can cancel the game from Manage game.'); return true; }
    queue=cancelParticipant(queue,'me',Date.now(),playerName);
  } else if (game.waitlistIds.includes('me')) {
    const offer=queue.offers.find(o=>o.userId==='me'&&o.status==='active');
    queue=offer?respondToOffer(queue,offer.id,'decline',Date.now(),playerName):
      {...queue,queue:queue.queue.filter(e=>e.userId!=='me')};
  } else if (game.playerIds.length+activeOfferCount(game)>=game.maxPlayers) {
    queue={...queue,queue:[...queue.queue.filter(e=>e.userId!=='me'),{userId:'me',status:'waiting'}]};
    showDialog('Waiting list joined','<p>Your position is #'+queue.queue.filter(e=>['waiting','offered'].includes(e.status)).length+'.</p>'+link('waitlist','View waiting list'));
  } else {
    queue={...queue,confirmed:[...queue.confirmed,'me'],openSpots:queue.openSpots.slice(1)};
    notify('You joined the game.');
  }
  applyQueue(game,queue);
  return true;
}

function showGameConfirmation(id) {
  const game=state.games.find(g=>g.id===id);
  if (!game || game.status==='cancelled' || !requirePlayer()) return;
  const title=game.playerIds.includes('me')?'Leave game':game.waitlistIds.includes('me')?'Leave waiting list':
    game.playerIds.length+activeOfferCount(game)>=game.maxPlayers?'Join waiting list':'Join game';
  showDialog(title,'<p>'+escape(game.title)+'</p><div class="row">'+actionButton('close','Go back')+actionButton('join-commit',title,id,'')+'</div>');
}

function waitingList() {
  const queue=state.games.filter(g=>g.status!=='cancelled'&&g.waitlistIds.includes('me'));
  const offers=state.games.flatMap(g=>(state.queues[g.id]?.offers||[]).filter(o=>o.userId==='me').map(o=>({game:g,offer:o})));
  return section('Smart waiting list','Track queue positions and review time-limited offers.',
    !state.signedIn?link('login','Log in'):
    '<p class="muted">Browser demo. SMS messages are previews. Real delivery and expiration require backend jobs.</p><h2>Invitations</h2>'+
    (offers.length?cards(offers.map(({game,offer})=>block(tag(game.status==='cancelled'?'Game cancelled':offer.status)+'<h3>'+escape(game.title)+'</h3>'+
      (offer.status==='active'&&game.status!=='cancelled'?'<strong data-expires="'+offer.expiresAt+'">'+formatCountdown(offer.expiresAt-Date.now())+'</strong>':'')+
      actionButton('offer','View invitation',game.id+'|'+offer.id)))):empty('No invitations yet.'))+
    '<h2 class="section">Queue positions</h2>'+
    (queue.length?cards(queue.map(g=>block('<h3>'+escape(g.title)+'</h3>'+tag('Queue position #'+(g.waitlistIds.indexOf('me')+1))+
      '<div class="row">'+link('games/'+g.id,'Game details','outline')+(g.playerIds.some(p=>p!==g.organizerId)?actionButton('vacancy','Simulate cancellation',g.id):'')+'</div>'))):
      empty('Join the waiting list from a full game. '+link('games','Find games'))));
}

function findOffer(value) {
  const [id,offerId]=value.split('|');
  const game=state.games.find(g=>g.id===id);
  const queue=state.queues[id];
  const offer=queue?.offers.find(o=>o.id===offerId&&o.userId==='me');
  return { game,queue,offer };
}

function showOffer(value) {
  const {game,queue,offer}=findOffer(value);
  if (!offer || !game || !requirePlayer()) return;
  const live=offer.status==='active'&&game.status!=='cancelled'&&Date.now()<offer.expiresAt;
  const sms=queue.sms.find(s=>s.to==='me'&&s.at===offer.sentAt);
  showDialog(live?'Waiting-list invitation':offer.status==='expired'?'Invitation expired':'Invitation status',
    '<h3>'+escape(game.title)+'</h3>'+tag(game.status==='cancelled'?'Game cancelled':offer.status)+
    (sms?'<p class="panel"><strong>SMS preview</strong><br>'+escape(sms.body)+'</p>':'')+
    (live?'<p><strong data-expires="'+offer.expiresAt+'">'+formatCountdown(offer.expiresAt-Date.now())+'</strong></p><div class="row">'+
      actionButton('decline-offer','Decline spot',value)+actionButton('accept-offer','Accept spot',value,'')+'</div>':''));
  dialog.dataset.offer=value;
}

function refreshOffers() {
  let changed=false;
  const now=Date.now();
  for (const game of state.games) {
    const queue=state.queues[game.id];
    if (!queue || game.status==='cancelled') continue;
    const next=expireOffers(queue,now,playerName);
    if (next!==queue) { applyQueue(game,next); changed=true; }
  }
  if (changed) {
    save();
    if (document.body.dataset.page === 'waitlist') render();
    if (dialog.open && dialog.dataset.offer) showOffer(dialog.dataset.offer);
  }
  document.querySelectorAll('[data-expires]').forEach(el=>el.textContent=formatCountdown(Number(el.dataset.expires)-now));
}


const assistantContext = new WeakMap();

function sendAssistantMessage(prompt, assistant) {
  const message = prompt.trim();
  if (!message) return;

  const thread = assistant.querySelector('.assistant-thread');
  const reply = assistantReply(message, assistant);
  thread.insertAdjacentHTML('beforeend', localizedMarkup(`<div class="assistant-turn">
    <div class="assistant-message assistant-message-user">
      <span class="assistant-speaker">You</span><p dir="auto" translate="no">${escape(message)}</p>
    </div>
    <div class="assistant-message assistant-message-bot">
      <span class="assistant-speaker">CourtBook</span>${reply}
    </div>
  </div>`));
  thread.scrollTop = thread.lastElementChild.offsetTop;
  const input = assistant.querySelector('[name=prompt]');
  input.value = '';
  input.focus({ preventScroll: true });
}

function assistantCourtCard(court, date, slots) {
  return `<article class="assistant-court">
    <div class="assistant-court-head">
      ${image(court.images[0],'','class="assistant-court-image" loading="lazy"')}
      <div>
        <h3><a href="${url(courtPath(court,date))}">${escape(court.facility)}</a></h3>
        <p class="muted">${escape(court.area)} · ${sportLabel(court.sport)}</p>
      </div>
      <span class="assistant-rate"><strong>${jod(court.pricePerHour)}</strong><small>/ hour</small></span>
    </div>
    <div class="assistant-court-actions">
      <div>
        <p>Pick an available time</p>
        <div class="row">${slots.slice(0,3).map(slot=>actionButton('assistant-slot',formatHour(slot.hour),court.id+'|'+date+'|'+slot.hour)).join('')}</div>
      </div>
      <a class="assistant-details" href="${url(courtPath(court,date))}">View court <span aria-hidden="true">→</span></a>
    </div>
  </article>`;
}

function assistantReply(prompt, assistant) {
  const normalize = value => value.toLowerCase()
    .replace(/[\u064b-\u065f\u0670]/g, '').replace(/[أإآ]/g,'ا').replace(/ى/g,'ي')
    .replace(/[٠-٩]/g, digit => String(digit.charCodeAt(0) - 0x660));
  const text = normalize(prompt);
  const includes = (label, alternative = uiText(label)) => text.includes(normalize(label)) || text.includes(normalize(alternative));
  const sport = SPORTS.find(item => includes(item.label))?.id;
  const area = AREAS.find(item => includes(item));
  const budget = text.match(/(?:under|below|up to|maximum|اقل من|تحت|لحد|ميزانيتي)\s*(\d+(?:[.٫]\d+)?)/)?.[1];
  const weekdays = [['sunday','الأحد'],['monday','الاثنين'],['tuesday','الثلاثاء'],['wednesday','الأربعاء'],['thursday','الخميس'],['friday','الجمعة'],['saturday','السبت']];
  const weekday = weekdays.findIndex(([label,alternative]) => includes(label,alternative));
  const dateOffset = includes('tomorrow','بكرا') || text.includes('غدا') ? 1 : includes('today','اليوم') ? 0 :
    weekday >= 0 ? (weekday - DEMO_TODAY.getDay() + 7) % 7 : undefined;
  const evening = /\bevening\b|\btonight\b|المسا|مساء|بالليل/.test(text) ? true :
    /\bany time\b|\ball day\b|اي وقت|كل اليوم/.test(text) ? false : undefined;
  const nearby = /\bclosest\b|\bnear me\b|اقرب|قريب مني/.test(text);
  const browse = /\bcourts?\b|\bbook\b|ملعب|احجز/.test(text);
  if (!sport && !area && budget === undefined && dateOffset === undefined && evening === undefined && !nearby && !browse) {
    return '<p>Tell me a sport, area, date, or budget. For example, “Padel in Abdoun tomorrow” or “Football under 30 JOD.”</p>';
  }

  const previous = sport ? {} : assistantContext.get(assistant) ?? {};
  const criteria = {
    sport: sport ?? previous.sport,
    area: area ?? previous.area,
    budget: budget !== undefined ? Number(budget.replace("٫",".")) : previous.budget,
    dateOffset: dateOffset ?? previous.dateOffset ?? 0,
    evening: evening ?? previous.evening ?? false,
    nearby: nearby || previous.nearby,
  };
  assistantContext.set(assistant, criteria);
  const date = toISODate(addDays(DEMO_TODAY, criteria.dateOffset));
  const matches = COURTS.filter(court =>
    (!criteria.sport || court.sport === criteria.sport) && (!criteria.area || court.area === criteria.area) &&
    (criteria.budget === undefined || court.pricePerHour <= criteria.budget)
  ).map(court => ({
    court,
    slots: freeSlots(court, date).filter(slot => !criteria.evening || slot.hour >= 18),
  })).filter(match => match.slots.length);
  if (criteria.nearby) matches.sort((a,b) => distanceKm(a.court,AMMAN_CENTER) - distanceKm(b.court,AMMAN_CENTER));
  if (!matches.length) return '<p>' + uiText('I couldn’t find an available court on :date. Try another day or a higher budget.', { date: formatDate(date) }) + '</p>';
  const label = matches.length === 1 ? uiText('1 court') : uiText(':count courts', { count: uiNumber.format(matches.length) });
  const introduction = uiText(criteria.nearby
    ? 'I found :courts near central Amman on :date. Pick a time to review a demo booking.'
    : 'I found :courts on :date. Pick a time to review a demo booking.',
    { courts: label, date: formatDate(date) });
  return `<p>${introduction}</p>
    <div class="stack assistant-chat-results">${matches.map(({court, slots}) => assistantCourtCard(court, date, slots)).join('')}</div>`;
}

const modalNames=['Login Required','Advanced Court Filters','Court Booking Summary','Booking Success','Booking Cancellation',
  'Join Public Game','Create Game Confirmation','Join Waiting List','Waiting List Invitation','Waiting List Invitation Expired',
  'Leave Game Confirmation','Subscription Pricing Breakdown','Subscription Confirmation','Notifications Popover','Court Image Gallery',
  'Edit Profile','AI Booking Confirmation','Location Permission Explanation'];

function modalLibrary() {
  return section('Modal library','Open each player-facing dialog in preview mode.',cards(modalNames.map((name,i)=>
    block('<h3>'+name+'</h3>'+actionButton('preview-modal','Open preview',String(i))))));
}

function previewModal(index) {
  const court=COURTS[0];
  const game=state.games[0];
  const details='<p>'+escape(court.facility)+' · '+formatDate(today)+' · '+formatHour(18)+' · '+jod(court.pricePerHour)+'</p>';
  const bodies=[
    '<p>Sign in to continue.</p>'+link('login','Log in'),'<p>Price, time range, setting and amenities.</p>'+link('courts','Explore filters'),
    details+actionButton('close','Confirm booking (preview)'),
    '<p>Reservation reference · CB-EXAMPLE</p>'+details+link('bookings','View my bookings'),
    details+actionButton('close','Cancel booking (preview)'),'<p>'+escape(game.title)+'</p>'+actionButton('close','Join game (preview)'),
    '<p>Review your proposed court, schedule and player capacity before publishing.</p>'+link('create-game','Open game builder'),
    '<p>Join the queue to receive the next eligible offer.</p>'+actionButton('close','Join waiting list (preview)'),
    '<p><strong>15:00</strong> to confirm your offer.</p><p>SMS preview: A spot is available for your CourtBook game!</p><div class="row">'+actionButton('close','Decline')+actionButton('close','Accept')+'</div>',
    '<p>This invitation expired and the offer passed to the next player.</p>'+link('waitlist','View invitations'),
    '<p>Leaving releases your spot to the next eligible player.</p>'+actionButton('close','Leave (preview)'),
    '<p>Hourly rate × duration × scheduled sessions, minus applicable discounts.</p>'+link('subscriptions','Open pricing calculator'),
    '<p>Review the monthly rate and schedule before confirming.</p>'+link('subscriptions','Open subscription builder'),
    '<p>Review unread booking updates and offers.</p>'+link('notifications','View notifications'),
    image(court.images[0],court.facility,'class="gallery"'),
    '<p>Update your photo, contact details, sports and SMS preferences.</p>'+link('profile','Open profile'),
    details+actionButton('close','Confirm demo booking (preview)'),
    '<p>Location permission is used to sort approved facilities by distance. You can search manually instead.</p>'+link('nearby','Open nearby courts')
  ];
  showDialog(modalNames[index],bodies[index]);
}

function playerPage(route,params) {
  switch(route) {
    case 'bookings':return myBookings(params);
    case 'my-games':return myGames(params);
    case 'profile':return profilePage();
    case 'notifications':return notificationPage();
    case 'subscriptions':return subscriptionsPage();
    case 'my-subscriptions':return mySubscriptions();
    case 'create-game':return createGamePage();
    case 'waitlist':return waitingList();
    case 'modals':return modalLibrary();
    default:return null;
  }
}

function handlePlayerAction(action,value,button) {
  switch(action) {
    case 'alerts':
      showDialog('Notifications', '<div class="stack">'+state.notifications.slice(0,4).map(n=>block(tag(n.read?n.time:'Unread')+'<strong>'+escape(n.title)+'</strong><p>'+escape(n.body)+'</p>')).join('')+link('notifications','All notifications')+'</div>');break;
    case 'locate':
      showDialog('Use your location?','<p>Your browser can sort the approved sample courts by your location. You can keep using the area filter if you decline.</p>'+actionButton('allow-location','Continue','',''));break;
    case 'allow-location':
      dialog.close();
      if(!navigator.geolocation){notify('Location unavailable. Choose an area manually.');break;}
      navigator.geolocation.getCurrentPosition(({coords})=>{
        if(!document.querySelector('#map-filters'))return;
        mapCenter={lat:coords.latitude,lng:coords.longitude};
        document.querySelector('#map-filters').elements.area.value='';
        document.querySelector('#map-status').textContent=uiText('Centered on your location');
        renderNearby();
      },()=>notify('Location unavailable. Choose an area manually.'),{timeout:8000});
      break;
    case 'recenter':
      document.querySelector('#map-filters').elements.area.value='';
      renderNearby();break;
    case 'more':
      showDialog('More from CourtBook','<div class="stack">'+[
        ['subscriptions','Subscriptions'],['my-subscriptions','My subscriptions'],['bookings','My bookings'],['my-games','My games'],
        ['waitlist','Waiting list'],['assistant','AI Assistant'],['notifications','Notifications'],['profile','Profile'],['modals','Modal library']
      ].map(([target,label])=>link(target,label,'outline')).join('')+'</div>'); break;
    case 'preview-modal':previewModal(Number(value));break;
    case 'booking-info': {
      const b=state.bookings.find(b=>b.ref===value);
      if (b&&requirePlayer()) showDialog('Booking details','<h3>'+escape(getCourt(b.courtId)?.facility)+'</h3><p>'+timeLine(b.date,b.startHour,b.duration)+'</p><p>'+escape(b.ref)+' · '+jod(b.total)+'</p>');
      break;
    }
    case 'read-all': state.notifications.forEach(n=>n.read=true);save();render();break;
    case 'read-alert': {
      const n=state.notifications.find(n=>n.id===value);
      if(n) n.read=true;
      save();render();break;
    }
    case 'logout':state.signedIn=false;save();location.href=url('login');break;
    case 'password':showDialog('Change password','<p>Secure password updates require the production authentication service.</p>');break;
    case 'edit-profile': {
      const p=state.profile;
      if (!requirePlayer()) break;
      showDialog('Edit profile',formBox('profile-form',
        field('Full name',formText('name',p.name,'text','maxlength="80"'))+field('Email',formText('email',p.email,'email'))+
        field('Phone number',formText('phone',p.phone,'tel'))+
        field('Profile photo','<input type="file" name="photo" accept="image/png,image/jpeg,image/webp">')+
        '<fieldset><legend>Preferred sports</legend>'+SPORTS.map(s=>'<label><input type="checkbox" name="sport" value="'+s.id+'"'+(p.preferredSports.includes(s.id)?' checked':'')+'>'+s.label+'</label>').join('')+'</fieldset>'+
        '<label><span><input type="checkbox" name="sms"'+(p.sms?' checked':'')+'> SMS notification preference</span></label>','Save profile'));
      break;
    }
    case 'pricing':showDialog('Subscription pricing breakdown',quoteMarkup(scheduleQuote(quoteFields(document.querySelector('#subscription-form')))));break;
    case 'confirm-sub': {
      if (!pendingSubscription || !requirePlayer()) break;
      const draft=pendingSubscription;
      pendingSubscription=null;
      const quote=scheduleQuote(draft);
      if (!quote?.scheduledCount || quote.conflicts.length) { showDialog('Schedule unavailable','<p>Choose a different weekly schedule.</p>');break; }
      state.subscriptions.unshift({...draft,id:'SUB-'+Date.now().toString(36).toUpperCase(),monthlyTotal:quote.total,sessionsTotal:quote.scheduledCount,
        sessionsUsed:0,sessionDates:quote.sessions.map(s=>s.date),status:'active',renews:false});
      addAlert('subscription_reminder','Demo subscription active',getCourt(draft.courtId).facility,'my-subscriptions');
      save();updateQuote();showDialog('Demo subscription confirmed','<p>'+jod(quote.total)+' · '+quote.scheduledCount+' sessions. No payment was collected.</p>'+link('my-subscriptions','View my subscriptions'));break;
    }
    case 'stop-sub':if(requirePlayer())showDialog('Cancel subscription?','<p>Release the remaining future demo sessions.</p><div class="row">'+actionButton('close','Keep subscription')+actionButton('confirm-stop','Cancel subscription',value,'')+'</div>');break;
    case 'confirm-stop': {
      const sub=state.subscriptions.find(s=>s.id===value);
      if(sub&&state.signedIn){sub.status='cancelled';sub.renews=false;save();dialog.close();render();notify('Subscription cancelled.');}break;
    }
    case 'wizard-back':wizard.step=Math.max(0,wizard.step-1);render();break;
    case 'publish-game':publishGame();break;
    case 'vacancy': {
      const game=state.games.find(g=>g.id===value&&g.status!=='cancelled'&&g.waitlistIds.includes('me'));
      const p=game?.playerIds.find(id=>id!==game.organizerId);
      if(p&&state.signedIn){applyQueue(game,cancelParticipant(queueFor(game),p,Date.now(),playerName));save();render();}
      break;
    }
    case 'offer':showOffer(value);break;
    case 'accept-offer':case 'decline-offer': {
      const {game,queue,offer}=findOffer(value);
      if(offer&&game.status!=='cancelled'&&state.signedIn){
        applyQueue(game,respondToOffer(queue,offer.id,action==='accept-offer'?'accept':'decline',Date.now(),playerName));save();dialog.close();render();
      }break;
    }
    case 'prompt': {
      sendAssistantMessage(value, button.closest('[data-assistant]'));
      break;
    }
    case 'assistant-slot': {
      const [id,date,hour]=value.split('|');
      const court=getCourt(id);
      const start=Number(hour);
      if (!requirePlayer() || !court || !freeSlots(court,date).some(s=>s.hour===start)) break;
      showDialog('Assistant booking confirmation','<h3>'+escape(court.facility)+'</h3><p>'+timeLine(date,start,1)+' · '+jod(court.pricePerHour)+'</p><p>Demo reservation. No payment is collected.</p>'+actionButton('assistant-confirm','Confirm demo booking',value,''));
      break;
    }
    case 'assistant-confirm': {
      const [id,date,hour]=value.split('|');
      const court=getCourt(id);
      if (!state.signedIn||!court||!freeSlots(court,date).some(s=>s.hour===Number(hour))) {showDialog('Slot unavailable','<p>Search for another time.</p>');break;}
      const b={ref:'CB-'+crypto.getRandomValues(new Uint32Array(1))[0].toString(36).toUpperCase(),courtId:id,date,startHour:Number(hour),duration:1,total:court.pricePerHour,status:'upcoming'};
      state.bookings.unshift(b);addAlert('booking_confirmed','Demo booking confirmed',b.ref,'bookings');save();
      showDialog('Demo booking confirmed','<p>Reference <strong>'+b.ref+'</strong></p>'+link('bookings','View my bookings'));break;
    }
    case 'manage-game': {
      const game=state.games.find(g=>g.id===value&&g.organizerId==='me'&&g.status!=='cancelled');
      if(game&&requirePlayer())showDialog('Manage game','<div class="stack">'+game.playerIds.filter(p=>p!=='me').map(p=>'<div class="row spread"><span>'+escape(playerName(p))+'</span>'+actionButton('confirm-remove','Remove player',value+'|'+p)+'</div>').join('')+
        formBox('capacity-form','<input type="hidden" name="game" value="'+escape(value)+'">'+field('Maximum players',formText('capacity',game.maxPlayers,'number','min="2" max="40"')),'Update capacity')+
        actionButton('confirm-game-cancel','Cancel game',value)+'</div>');break;
    }
    case 'confirm-remove':showDialog('Remove player?','<p>The player will lose their place in this game.</p>'+actionButton('remove-player','Remove player',value,''));break;
    case 'confirm-game-cancel':showDialog('Cancel game?','<p>This will close signups and offers.</p>'+actionButton('cancel-game','Cancel game',value,''));break;
    case 'remove-player':case 'cancel-game': {
      const [id,player]=value.split('|');
      const game=state.games.find(g=>g.id===id&&g.organizerId==='me'&&g.status!=='cancelled');
      if (game&&state.signedIn) {
        if(action==='cancel-game')game.status='cancelled';
        else if(player!=='me')applyQueue(game,cancelParticipant(queueFor(game),player,Date.now(),playerName));
        save();dialog.close();render();
      }break;
    }
    default:return false;
  }
  return true;
}



function publishGame() {
  const w=wizard;
  if(!w||w.step!==3||!requirePlayer())return;
  const booking=state.bookings.find(b=>b.ref===w.reservation&&b.status==='upcoming'&&b.courtId===w.court&&
    !state.games.some(g=>g.bookingRef===b.ref&&g.status!=='cancelled'));
  const court=getCourt(w.court);
  const duration=Number(w.duration);
  const start=Number(w.hour);
  const slots=freeSlots(court,w.date);
  if(!booking&&(w.reservation||!Array.from({length:duration},(_,i)=>start+i).every(h=>slots.some(s=>s.hour===h)))){
    wizard.step=1;dialog.close();render();notify('The selected time is unavailable.');return;
  }
  const id='game-'+crypto.getRandomValues(new Uint32Array(1))[0].toString(36);
  state.games.unshift({id,title:w.title.trim(),description:w.description.trim(),sport:w.sport,courtId:w.court,
    date:w.date,startHour:start,duration,skill:w.skill,minimumPlayers:Number(w.minimum),maxPlayers:Number(w.capacity),
    organizerId:'me',playerIds:['me'],waitlistIds:[],pricePerPlayer:Number(w.price),bookingRef:booking?.ref,
    bookingStatus:booking?'confirmed':'proposed',status:'open'});
  save();location.href=url('games/'+id);
}

function submitWizard(data) {
  if (!requirePlayer()) return;
  if(wizard.step===3){
    showDialog('Publish this public game?',gameSummary(wizard)+'<div class="row">'+actionButton('close','Keep editing')+actionButton('publish-game','Create game','','')+'</div>');return;
  }
  Object.assign(wizard,Object.fromEntries(data));
  if(wizard.step===0&&(!wizard.title.trim()||!wizard.description.trim())){notify('Enter a title and description.');return;}
  if(wizard.step===1){
    const booking=state.bookings.find(b=>b.ref===wizard.reservation&&b.status==='upcoming'&&b.courtId===wizard.court);
    if(booking)Object.assign(wizard,{date:booking.date,hour:String(booking.startHour),duration:booking.duration});
    else if(!wizard.hour){notify('Choose a start time.');return;}
  }
  if(wizard.step===2&&Number(wizard.minimum)>Number(wizard.capacity)){notify('Minimum players cannot exceed capacity.');return;}
  wizard.step++;
  render();
}

async function saveProfile(data) {
  if(!state.signedIn)return;
  const name=String(data.get('name')).trim();
  if(!name){notify('Enter your full name.');return;}
  const file=data.get('photo');
  let photo=state.profile.photo;
  if (file?.size) {
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>500000){notify('Choose a JPEG, PNG or WebP under 500 KB.');return;}
    try {
      photo=await new Promise((resolve,reject)=>{const reader=new FileReader();reader.onload=()=>resolve(reader.result);reader.onerror=reject;reader.readAsDataURL(file);});
    }catch{notify('Unable to read the photo.');return;}
  }
  Object.assign(state.profile,{name,email:String(data.get('email')),phone:String(data.get('phone')),
    preferredSports:data.getAll('sport'),sms:data.has('sms'),photo});
  save();dialog.close();render();notify('Profile updated.');
}

function handlePlayerForm(form,data) {
  if (form.hasAttribute('data-assistant-search')) {
    sendAssistantMessage(String(data.get('prompt')), form.closest('[data-assistant]'));
    return true;
  }
  switch(form.id) {
    case 'auth-forgot-password':
      showDialog('Recovery preview','<p>Email delivery requires the production account service. No email was sent.</p>');break;
    case 'auth-register':
      if(data.get('password')!==data.get('confirm')){notify('Passwords must match.');break;}
      if(!String(data.get('name')).trim()){notify('Enter a name.');break;}
      Object.assign(state.profile,{name:String(data.get('name')).trim(),email:String(data.get('email')),phone:String(data.get('phone'))});
      state.signedIn=true;save();location.href=url('bookings');break;
    case 'auth-login':
      state.signedIn=true;save();location.href=url('bookings');break;
    case 'profile-form':saveProfile(data);break;
    case 'subscription-form': {
      if(!requirePlayer())break;
      const draft=quoteFields(form);
      const quote=scheduleQuote(draft);
      if(!quote?.scheduledCount||quote.conflicts.length){notify('Select a schedule with all slots available.');break;}
      pendingSubscription=draft;
      showDialog('Confirm monthly subscription',quoteMarkup(quote)+'<p>No payment will be collected in this demo.</p>'+actionButton('confirm-sub','Confirm demo subscription','',''));
      break;
    }
    case 'game-wizard':submitWizard(data);break;
    case 'capacity-form': {
      const game=state.games.find(g=>g.id===data.get('game')&&g.organizerId==='me'&&g.status!=='cancelled');
      const capacity=Number(data.get('capacity'));
      if(!game||!state.signedIn)break;
      const occupied=game.playerIds.length+activeOfferCount(game);
      if(!Number.isInteger(capacity)||capacity<Math.max(2,occupied,game.minimumPlayers||2)||capacity>40){notify('Capacity must cover the minimum, confirmed players and active offers.');break;}
      const queue=resizeWaitlist(queueFor(game),capacity,Date.now(),playerName);
      game.maxPlayers=capacity;applyQueue(game,queue);
      save();dialog.close();render();break;
    }
    default:return false;
  }
  return true;
}

document.addEventListener('change',event=>{
  const input=event.target;
  if(input.form?.id==='subscription-form'){
    if(input.name==='sub-sport')input.form.elements.court.innerHTML=localizedMarkup(COURTS.filter(c=>!input.value||input.value===c.sport).map(c=>option(c.id,c.facility+' · '+c.name)).join(''));
    updateQuote();
  }
  if(input.form?.id==='game-wizard' && wizard.step===1 && ['court','date','duration','reservation'].includes(input.name)){
    Object.assign(wizard,Object.fromEntries(new FormData(input.form)));
    wizard.hour='';
    if(input.name==='court')wizard.reservation='';
    render();
  }
});

