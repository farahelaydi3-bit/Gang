

/** Facility-configured subscription discount tiers (by sessions in the billing period). */
const DISCOUNT_TIERS = [
  { minSessions: 12, rate: 0.15, label: '12+ sessions' },
  { minSessions: 8, rate: 0.1, label: '8–11 sessions' },
  { minSessions: 4, rate: 0.05, label: '4–7 sessions' },
]

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

                                 
                         
                                                  
                        
                       
                  
                      
                              
                  
               
                   
 

const round2 = (n        ) => Math.round(n * 100) / 100

/**
 * Deterministic pricing. The AI assistant may explain this result but never computes it.
 * Sessions that clash with existing bookings are excluded and never reserved.
 */
function quoteSubscription(input   
                 
                
                   
                  
                   
 )                           {
  const court = getCourt(input.courtId)
  if (!court) return null
  const start = fromISODate(input.startDate)
  const end = new Date(start)
  end.setMonth(end.getMonth() + 1)

  const sessions                                         = []
  for (let d = new Date(start); d < end; d = addDays(d, 1)) {
    if (input.days.includes(d.getDay())) {
      const iso = toISODate(d)
      sessions.push({ date: iso, available: isRangeAvailable(court.id, iso, input.startHour, input.duration) })
    }
  }
  const scheduled = sessions.filter((s) => s.available).length
  const pricePerSession = round2(court.pricePerHour * input.duration)
  const subtotal = round2(scheduled * pricePerSession)
  const tier = DISCOUNT_TIERS.find((t) => scheduled >= t.minSessions)
  const discountRate = tier?.rate ?? 0
  const discount = round2(subtotal * discountRate)
  return {
    pricePerSession,
    sessions,
    scheduledCount: scheduled,
    conflictCount: sessions.length - scheduled,
    subtotal,
    discountRate,
    discountLabel: tier?.label ?? null,
    discount,
    total: round2(subtotal - discount),
    periodEnd: toISODate(addDays(end, -1)),
  }
}

/**
 * Reference model of the server-side waiting-list rules.
 * In production this logic lives in a Laravel job + scheduler; the browser only renders state.
 *
 * Invariants:
 * - Each open spot is offered to exactly one person at a time.
 * - confirmed + active offers never exceeds capacity.
 * - Every offer has its own independent 15-minute expiry.
 */

const OFFER_WINDOW_MS = 15 * 60 * 1000

                                                                                                                
                     
            
                
                
                
                   
                                                        
 
                                                                         
                                                               

                             
                  
                     
                     
                     
                 
               
                    
             
 

const SMS_TEMPLATE = 'A spot is available for your CourtBook game! Confirm within 15 minutes: courtbook.jo/c/'

function createWaitlist(capacity        , confirmed          , queue          )                {
  return {
    capacity,
    confirmed,
    queue: queue.map((userId) => ({ userId, status: 'waiting' })),
    openSpots: [],
    offers: [],
    sms: [],
    events: [],
    seq: 1,
  }
}

const log = (s               , at        , text        )                => ({
  ...s,
  events: [{ id: `e${s.seq}`, at, text }, ...s.events],
  seq: s.seq + 1,
})

function dispatch(state               , now        , nameOf                        )                {
  let s = state
  while (s.openSpots.length > 0) {
    const active = s.offers.filter((o) => o.status === 'active').length
    if (s.confirmed.length + active >= s.capacity) break
    const next = s.queue.find((q) => q.status === 'waiting')
    if (!next) break
    const [spotId, ...rest] = s.openSpots
    const code = `${spotId}${next.userId}`.toUpperCase()
    const offer        = {
      id: `o${s.seq}`,
      spotId,
      userId: next.userId,
      sentAt: now,
      expiresAt: now + OFFER_WINDOW_MS,
      status: 'active',
    }
    s = {
      ...s,
      openSpots: rest,
      offers: [...s.offers, offer],
      queue: s.queue.map((q) => (q.userId === next.userId ? { ...q, status: 'offered' } : q)),
      sms: [{ id: `s${s.seq}`, to: next.userId, body: SMS_TEMPLATE + code, at: now }, ...s.sms],
      seq: s.seq + 1,
    }
    s = log(s, now, `SMS sent to ${nameOf(next.userId)} — 15-minute offer for spot ${spotId}`)
  }
  return s
}

function cancelParticipant(state               , userId        , now        , nameOf                        ) {
  if (!state.confirmed.includes(userId)) return state
  let s                = {
    ...state,
    confirmed: state.confirmed.filter((id) => id !== userId),
    openSpots: [...state.openSpots, `S${state.seq}`],
    seq: state.seq + 1,
  }
  s = log(s, now, `${nameOf(userId)} cancelled — spot opened`)
  return dispatch(s, now, nameOf)
}

function respondToOffer(
  state               ,
  offerId        ,
  response                      ,
  now        ,
  nameOf                        ,
) {
  const offer = state.offers.find((o) => o.id === offerId)
  if (!offer || offer.status !== 'active') return state
  if (now >= offer.expiresAt) return expireOffers(state, now, nameOf)

  if (response === 'accept') {
    if (state.confirmed.length >= state.capacity) return state
    const s                = {
      ...state,
      confirmed: [...state.confirmed, offer.userId],
      offers: state.offers.map((o) => (o.id === offerId ? { ...o, status: 'accepted' } : o)),
      queue: state.queue.map((q) => (q.userId === offer.userId ? { ...q, status: 'accepted' } : q)),
    }
    return log(s, now, `${nameOf(offer.userId)} accepted and joined the game`)
  }

  let s                = {
    ...state,
    offers: state.offers.map((o) => (o.id === offerId ? { ...o, status: 'declined' } : o)),
    queue: state.queue.map((q) => (q.userId === offer.userId ? { ...q, status: 'declined' } : q)),
    openSpots: [offer.spotId, ...state.openSpots],
  }
  s = log(s, now, `${nameOf(offer.userId)} declined — passing spot ${offer.spotId} to next in line`)
  return dispatch(s, now, nameOf)
}

function expireOffers(state               , now        , nameOf                        ) {
  const expiring = state.offers.filter((o) => o.status === 'active' && now >= o.expiresAt)
  if (expiring.length === 0) return state
  let s = state
  for (const offer of expiring) {
    s = {
      ...s,
      offers: s.offers.map((o) => (o.id === offer.id ? { ...o, status: 'expired' } : o)),
      queue: s.queue.map((q) => (q.userId === offer.userId ? { ...q, status: 'expired' } : q)),
      openSpots: [...s.openSpots, offer.spotId],
    }
    s = log(s, offer.expiresAt, `Offer to ${nameOf(offer.userId)} expired — passing spot ${offer.spotId} on`)
  }
  return dispatch(s, now, nameOf)
}

const formatCountdown = (ms        ) => {
  const total = Math.max(0, Math.ceil(ms / 1000))
  const m = Math.floor(total / 60)
  const sec = total % 60
  return `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
}

function resizeWaitlist(state, capacity, now, nameOf) {
  const active = state.offers.filter(offer => offer.status === 'active').length;
  const free = Math.max(0, capacity - state.confirmed.length - active);
  const kept = state.openSpots.slice(0, free);
  const extra = Array.from({ length: free - kept.length }, (_, index) => 'S' + (state.seq + index));
  return dispatch({ ...state, capacity, openSpots: [...kept, ...extra], seq: state.seq + extra.length }, now, nameOf);
}

