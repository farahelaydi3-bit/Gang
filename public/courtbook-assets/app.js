"use strict";

const main =
    document.querySelector("#courtbook-demo") ??
    document.querySelector("#main");
const hasBladeHeading = Boolean(main.closest("[data-blade-heading]"));
const dialog = document.querySelector("dialog");
const today = toISODate(DEMO_TODAY);
const storageKey = "courtbook-blade-demo-v1";
let state = {
    bookings: structuredClone(INITIAL_BOOKINGS),
    games: structuredClone(GAMES),
};
try {
    const saved = JSON.parse(localStorage.getItem(storageKey));
    if (Array.isArray(saved?.bookings) && Array.isArray(saved?.games))
        state = saved;
} catch {
    /* Browser storage may be unavailable. */
}

initializePlayerState(state);

const escape = (value) =>
    String(value ?? "").replace(
        /[&<>"']/g,
        (c) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
            })[c],
    );
const tag = (value) => '<span class="tag">' + escape(value) + "</span>";
const link = (target, text, style = "") =>
    `<a class="button ${style}" href="${url(target)}">${text}</a>`;
const courtPath = (court, date = today) => `courts/${court.id}?date=${date}`;
const option = (value, label = value, selected = "") =>
    `<option value="${escape(value)}"${value === selected ? " selected" : ""}>${escape(label)}</option>`;
const sports = (selected = "") =>
    option("", "All sports", selected) +
    SPORTS.map((s) => option(s.id, s.label, selected)).join("");
const areas = (selected = "") =>
    option("", "All areas", selected) +
    AREAS.map((a) => option(a, a, selected)).join("");
const heading = (label, title, description) =>
    hasBladeHeading
        ? ""
        : `<div class="page-heading"><span class="eyebrow">${label}</span><h1>${title}</h1><p>${description}</p></div>`;
const empty = (message) => `<div class="empty">${message}</div>`;
const page = (content) =>
    hasBladeHeading ? content : `<div class="wrap page">${content}</div>`;
const image = (url, alt, attrs = "") =>
    `<img src="${escape(assetUrl(url))}" alt="${escape(alt)}" ${attrs}>`;
const dateInput = (date = today) =>
    `<input aria-label="Booking date" name="date" type="date" min="${today}" value="${escape(date)}" required>`;
const validDate = (value) =>
    /^\d{4}-\d{2}-\d{2}$/.test(value ?? "") && value >= today ? value : today;
let filters;
let selection;
let noticeTimer;

function notify(message) {
    const notice = document.querySelector("#notice");
    notice.textContent = uiText(message);
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => {
        notice.textContent = "";
    }, 4500);
}

function save() {
    try {
        localStorage.setItem(storageKey, JSON.stringify(state));
    } catch {
        notify("Changes are available for this session only.");
    }
}

function showDialog(title, content) {
    delete dialog.dataset.offer;
    dialog.innerHTML = localizedMarkup(`<div class="row spread dialog-head"><h2 id="dialog-title">${title}</h2><button class="outline" data-action="close" aria-label="Close">×</button></div>${content}`);
    if (!dialog.open) dialog.showModal();
}

function freeSlots(court, date) {
    return getSlots(court.id, date).filter(
        (slot) =>
            slot.state === "available" &&
            !hasSubscriptionSlot(court.id, date, slot.hour) &&
            !state.bookings.some(
                (b) =>
                    b.courtId === court.id &&
                    b.date === date &&
                    b.status === "upcoming" &&
                    slot.hour >= b.startHour &&
                    slot.hour < b.startHour + b.duration,
            ),
    );
}

function courtCard(court, date = today) {
    const count = freeSlots(court, date).length;
    return `<article class="card">
    <a class="card-photo" href="${url(courtPath(court, date))}">${image(court.images[0], court.facility, 'loading="lazy"')}${tag(sportLabel(court.sport))}</a>
    <div class="card-body"><div class="row spread"><span class="muted">${escape(court.area)}</span><span class="rating">★ ${court.rating}</span></div>
    <h3><a href="${url(courtPath(court, date))}">${escape(court.facility)}</a></h3>
    <p class="muted">${escape(court.name)} · ${court.indoor ? "Indoor" : "Outdoor"}</p>
    <div class="row spread"><span class="price">${jod(court.pricePerHour)} <small class="muted">/ hr</small></span>${tag(count ? count + " slots left" : "Fully booked")}</div>
    ${link(courtPath(court, date), "View court", "outline")}</div></article>`;
}

function renderHome() {
    document.querySelector("#home-sport").innerHTML = localizedMarkup(sports());
    document.querySelector("#home-area").innerHTML = localizedMarkup(areas());
    const date = document.querySelector("#home-date");
    date.min = today;
    date.value = today;
    document.querySelector("#home-sports").innerHTML = localizedMarkup(SPORTS.map(
        (s) =>
            `<a class="sport" href="${url("courts?sport=" + s.id)}">${image(s.image, "", 'loading="lazy"')}<strong>${s.label}</strong></a>`,
    ).join(""));
    document.querySelector("#home-courts").innerHTML = localizedMarkup(COURTS.slice(0, 4)
        .map((c) => courtCard(c))
        .join(""));
    document.querySelector("#home-games").innerHTML = localizedMarkup(state.games
        .slice(0, 4)
        .map(gameCard)
        .join(""));
    document
        .querySelector("#home-map-court")
        .insertAdjacentHTML(
            "beforeend",
            localizedMarkup(COURTS.map((c) => option(c.id, c.facility)).join("")),
        );
    document.querySelector("#home-map").src = mapEmbedUrl(AMMAN_CENTER, 0.085);
}

function updateCourtControls() {
    document.querySelector("#court-sports").innerHTML = localizedMarkup([
        { id: "", label: "All sports" },
        ...SPORTS,
    ]
        .map(
            (s) =>
                `<button type="button" class="chip" data-action="sport" data-value="${s.id}" aria-pressed="${filters.sport === s.id}">${s.label}</button>`,
        )
        .join(""));
    document.querySelector("#court-area").innerHTML = localizedMarkup(areas(filters.area));
    const date = document.querySelector("#court-date");
    date.min = today;
    date.value = filters.date;
    main.querySelector('[data-action="available"]').setAttribute(
        "aria-pressed",
        String(filters.available),
    );
}

function renderResults() {
    const matches = COURTS.filter(
        (c) =>
            (!filters.sport || c.sport === filters.sport) &&
            (!filters.area || c.area === filters.area) &&
            c.pricePerHour <= filters.price &&
            (!filters.setting || (filters.setting === "indoor") === c.indoor) &&
            filters.amenities.every((a) => c.amenities.includes(a)) &&
            (!(filters.available || filters.from !== 6 || filters.to !== 24) ||
                freeSlots(c, filters.date).some(
                    (slot) =>
                        slot.hour >= filters.from && slot.hour < filters.to,
                )),
    );
    document.querySelector("#court-results").innerHTML =
        localizedMarkup(`<div class="row spread results"><span class="muted" role="status"><strong>${matches.length}</strong> courts found</span>
    ${filters.amenities.map(tag).join("")}</div>` +
        (matches.length
            ? `<div class="grid">${matches.map((c) => courtCard(c, filters.date)).join("")}</div>`
            : empty(
                  '<p>No courts match these filters.</p><button class="outline" data-action="reset">Reset filters</button>',
              )));
}

function showFilters() {
    showDialog(
        "Advanced filters",
        `<form class="stack" id="advanced">
    <label>Max price per hour (JOD)<input name="price" type="number" min="0" max="100" value="${filters.price}" required></label>
    <label>Setting<select name="setting">${option("", "Any setting", filters.setting)}${option("indoor", "Indoor", filters.setting)}${option("outdoor", "Outdoor", filters.setting)}</select></label>
    <div class="row"><label>From<select name="from">${hourOptions(6, 23, filters.from)}</select></label><label>Until<select name="to">${hourOptions(7, 24, filters.to)}</select></label></div>
    <fieldset><legend>Amenities</legend>${AMENITIES.map((a) => `<div><input id="amenity-${AMENITIES.indexOf(a)}" type="checkbox" name="amenity" value="${escape(a)}"${filters.amenities.includes(a) ? " checked" : ""}> <label style="display:inline" for="amenity-${AMENITIES.indexOf(a)}">${escape(a)}</label></div>`).join("")}</fieldset>
    <div class="row spread"><button type="button" class="outline" data-action="reset">Reset filters</button><button>Show results</button></div></form>`,
    );
}

function hourOptions(first, last, selected) {
    return Array.from({ length: last - first + 1 }, (_, i) =>
        option(first + i, formatHour(first + i), selected),
    ).join("");
}

function courtDetails(court) {
    return page(`<a class="muted" href="${url("courts")}">← Explore courts</a>
    ${heading(sportLabel(court.sport), escape(court.facility), escape(court.name + " · " + court.address))}
    <div class="details"><section>${image(court.images[0], court.facility, 'class="gallery" id="gallery"')}
    <div class="row thumbs">${court.images.map((src, i) => `<button class="outline" data-action="gallery" data-value="${i}" aria-label="Show court photo ${i + 1}">${image(src, "")}</button>`).join("")}</div>
    <div class="row">${tag(court.indoor ? "Indoor" : "Outdoor")}${tag("Approved facility")}<span class="rating">★ ${court.rating} (${court.reviews} reviews)</span></div>
    <h2>About this court</h2><p>${escape(court.description)}</p>
    <h2>Location</h2><p><a href="${url("nearby?court=" + court.id)}">${escape(court.address)}</a></p>
    <h2>Amenities</h2><div class="tags">${court.amenities.map(tag).join("")}</div>
    <h2>Hosted by ${escape(court.owner.name)}</h2><p class="muted">${escape(court.owner.responseTime)}</p></section>
    <aside class="panel stack booking-widget" id="book"><h2>Book this court</h2><div class="price">${jod(court.pricePerHour)} / hour</div>
    ${dateInput(selection.date)}<div id="calendar-days" class="row"></div><label>Duration<select name="duration">${[1, 2, 3].map((n) => option(n, n + (n === 1 ? " hour" : " hours"), selection.duration)).join("")}</select></label>
    <div id="booking-slots"></div></aside></div>`);
}

function availableStarts(court) {
    const hours = new Set(freeSlots(court, selection.date).map((s) => s.hour));
    return [...hours].filter((hour) =>
        Array.from(
            { length: selection.duration },
            (_, offset) => hour + offset,
        ).every((h) => hours.has(h)),
    );
}

function renderSlots() {
    const calendar = document.querySelector("#calendar-days");
    if (calendar)
        calendar.innerHTML = localizedMarkup(Array.from({ length: 7 }, (_, i) => {
            const date = toISODate(addDays(DEMO_TODAY, i));
            return `<button class="chip" data-action="calendar-day" data-value="${date}" aria-pressed="${date === selection.date}">${formatDate(date, { weekday: "short", day: "numeric" })}</button>`;
        }).join(""));
    const starts = availableStarts(selection.court);
    if (!starts.includes(selection.hour)) selection.hour = null;
    document.querySelector("#booking-slots").innerHTML = localizedMarkup(starts.length
        ? `<div class="stack"><div class="slots">${starts.map((h) => `<button class="chip" data-action="slot" data-value="${h}" aria-pressed="${selection.hour === h}">${formatHour(h)}</button>`).join("")}</div>
      <button data-action="review" ${selection.hour === null ? "disabled" : ""}>Review booking · ${jod(selection.court.pricePerHour * selection.duration)}</button></div>`
        : empty("No available slots for this date and duration."));
}

function gameCard(game) {
    const court = getCourt(game.courtId);
    const spots = Math.max(
        0,
        game.maxPlayers - game.playerIds.length - activeOfferCount(game),
    );
    return `<article class="card"><a class="card-photo" href="${url("games/" + game.id)}">${image(court.images[0], court.facility, 'loading="lazy"')}${tag(sportLabel(game.sport))}</a>
    <div class="card-body"><h3>${escape(game.title)}</h3><p class="muted">${escape(court.facility)} · ${formatDate(game.date)} · ${formatHour(game.startHour)}</p>
    <p class="muted">By ${escape(playerName(game.organizerId))} · ${game.playerIds.length}/${game.maxPlayers} players</p><progress aria-label="Confirmed players" value="${game.playerIds.length}" max="${game.maxPlayers}"></progress>
    <div class="row spread">${tag(game.status === "cancelled" ? "Cancelled" : spots ? spots + " spots left" : "Waitlist")}<strong>${jod(game.pricePerPlayer)}</strong></div>
    ${link("games/" + game.id, "Game details", "outline")}</div></article>`;
}

function gameResults() {
    const form = document.querySelector("#game-filters");
    const filters = form ? new FormData(form) : new URLSearchParams();
    const matches = state.games.filter((g) => {
        const court = getCourt(g.courtId);
        return (
            (!filters.get("sport") || g.sport === filters.get("sport")) &&
            (!filters.get("area") || court.area === filters.get("area")) &&
            (!filters.get("date") || g.date === filters.get("date")) &&
            (!filters.get("price") ||
                g.pricePerPlayer <= Number(filters.get("price"))) &&
            (!filters.get("distance") ||
                distanceKm(court, AMMAN_CENTER) <=
                    Number(filters.get("distance"))) &&
            (!filters.has("spots") ||
                (g.status !== "cancelled" &&
                    g.playerIds.length + activeOfferCount(g) < g.maxPlayers))
        );
    });
    document.querySelector("#game-results").innerHTML = localizedMarkup(matches.length
        ? matches.map(gameCard).join("")
        : empty("No games match these filters."));
}

function gameDetails(game) {
    const court = getCourt(game.courtId);
    const joined = game.playerIds.includes("me");
    const waiting = game.waitlistIds.includes("me");
    const full =
        game.playerIds.length + activeOfferCount(game) >= game.maxPlayers;
    const action = joined
        ? "Leave game"
        : waiting
          ? "Leave waitlist"
          : full
            ? "Join waitlist"
            : "Join game";
    return page(`<a class="muted" href="${url("games")}">← Find games</a>${heading(sportLabel(game.sport), escape(game.title), escape(court.facility))}
    <div class="details game-details"><section>${image(court.images[0], court.facility, 'class="gallery"')}<h2>About this game</h2><p>${escape(game.description)}</p>
    ${link(courtPath(court, game.date), "View court", "outline")}</section><aside class="panel stack game-info">
    <dl class="game-schedule" aria-label="Game schedule">
      <div><dt>Date</dt><dd><time datetime="${escape(game.date)}">${formatDate(game.date, { weekday: "long", day: "numeric", month: "long" })}</time></dd></div>
      <div><dt>Time · Amman</dt><dd>${formatHour(game.startHour)} – ${formatHour(game.startHour + game.duration)}</dd></div>
    </dl>
    <h2>Players · ${game.playerIds.length}/${game.maxPlayers}</h2>
    <div class="tags">${game.playerIds.map((id) => tag(playerName(id))).join("")}</div>
    <div>${tag(game.skill)} ${tag(game.bookingStatus === "confirmed" ? "Court reserved" : "Court proposed")}</div>
    <strong>${jod(game.pricePerPlayer)} / player</strong>
    ${game.status === "cancelled" ? tag("Cancelled") : game.organizerId === "me" ? actionButton("manage-game", "Manage game", game.id, "") : `<button data-action="join" data-value="${game.id}">${action}</button>`}
    ${waiting ? tag("Queue position #" + (game.waitlistIds.indexOf("me") + 1)) + link("waitlist", "View waiting list", "outline") : ""}</aside></div>`);
}

let mapCenter = AMMAN_CENTER;

function renderNearby() {
    const form = document.querySelector("#map-filters");
    const data = new FormData(form);
    const area = data.get("area");
    const sport = data.get("sport");
    const max = Number(data.get("price"));
    const distance = Number(data.get("distance"));
    const courtInArea = COURTS.find((c) => c.area === area);
    const center = courtInArea || mapCenter;
    const list = COURTS.map((c) => ({ court: c, km: distanceKm(c, center) }))
        .filter(
            ({ court, km }) =>
                (!area || court.area === area) &&
                (!sport || court.sport === sport) &&
                court.pricePerHour <= max &&
                km <= distance,
        )
        .sort((a, b) => a.km - b.km);
    document.querySelector("#nearby-results").innerHTML = localizedMarkup(list.length
        ? list
              .map(
                  ({ court: c, km }) =>
                      `<article class="panel"><div class="row spread"><strong>${escape(c.facility)}</strong>${tag(sportLabel(c.sport))}</div>
       <p class="muted">${escape(c.area)} · ${km.toFixed(1)} km · ${jod(c.pricePerHour)} / hr</p>
       <div class="row"><button class="outline" data-action="map" data-value="${c.id}">Show on map</button>${link(courtPath(c), "View court")}</div></article>`,
              )
              .join("")
        : empty("No courts match these map filters."));
    updateMap(courtInArea);
}

function mapEmbedUrl({ lat, lng }, span, showMarker = false) {
    const params = new URLSearchParams({
        bbox: [lng - span, lat - span / 2, lng + span, lat + span / 2].join(
            ",",
        ),
        layer: "mapnik",
    });
    if (showMarker) params.set("marker", lat + "," + lng);
    return "https://www.openstreetmap.org/export/embed.html?" + params;
}

function updateMap(court) {
    const center = court ?? mapCenter;
    const { lat, lng } = center;
    document.querySelector("#map").src = mapEmbedUrl(
        center,
        court ? 0.013 : 0.085,
        true,
    );
    document.querySelector("#map-selection").innerHTML = localizedMarkup(court
        ? block(
              "<h3>" +
                  escape(court.facility) +
                  "</h3><p>" +
                  sportLabel(court.sport) +
                  " · " +
                  jod(court.pricePerHour) +
                  ' / hr</p><div class="row">' +
                  link(courtPath(court), "View details", "outline") +
                  link(courtPath(court), "Book now") +
                  '<a class="button outline" href="https://www.google.com/maps/dir/?api=1&destination=' +
                  lat +
                  "," +
                  lng +
                  '" target="_blank" rel="noopener noreferrer">Get directions</a></div>',
          )
        : "");
}

function defaultFilters(params = new URLSearchParams()) {
    return {
        sport: SPORTS.some((s) => s.id === params.get("sport"))
            ? params.get("sport")
            : "",
        area: AREAS.includes(params.get("area")) ? params.get("area") : "",
        date: validDate(params.get("date")),
        available: false,
        price: 100,
        setting: "",
        amenities: [],
        from: 6,
        to: 24,
    };
}

function render() {
    const target = currentPage();
    const [path, query] = target.split("?");
    if (path === "main") return;
    const [route, id] = path.split("/");
    const params = new URLSearchParams(query);
    const court = id && getCourt(id);
    const game = id && state.games.find((g) => g.id === id);
    if (route === "home") {
        renderHome();
        return;
    }
    if (["assistant", "login", "register", "forgot-password"].includes(route))
        return;
    if (route === "courts" && !id) {
        filters = defaultFilters(params);
        updateCourtControls();
        renderResults();
    } else if (route === "courts" && court) {
        selection = {
            court,
            date: validDate(params.get("date")),
            duration: 1,
            hour: null,
        };
        main.innerHTML = localizedMarkup(courtDetails(court));
        renderSlots();
    } else if (route === "games" && !id) {
        main.querySelector("#game-filters [name=sport]").innerHTML = localizedMarkup(sports());
        main.querySelector("#game-filters [name=area]").innerHTML = localizedMarkup(areas());
        gameResults();
    } else if (route === "games" && game) main.innerHTML = localizedMarkup(gameDetails(game));
    else if (route === "nearby") {
        main.querySelector("#map-filters [name=sport]").innerHTML = localizedMarkup(sports());
        main.querySelector("#map-filters [name=area]").innerHTML = localizedMarkup(areas());
        renderNearby();
        if (getCourt(params.get("court")))
            updateMap(getCourt(params.get("court")));
    } else
        main.innerHTML =
            localizedMarkup(playerPage(route, params) ??
            page(empty("Page not found. " + link("home", "Back to home"))));
    updateQuote();
}

document.addEventListener("change", ({ target }) => {
    if (!main.contains(target)) return;
    if (target.id === "home-map-court") {
        const court = getCourt(target.value);
        document.querySelector("#home-map").src = mapEmbedUrl(
            court ?? AMMAN_CENTER,
            court ? 0.013 : 0.085,
            Boolean(court),
        );
        const details = document.querySelector("#home-map-details");
        details.href = court ? url(courtPath(court)) : url("nearby");
        details.hidden = !court;
        return;
    }
    if (target.form?.id === "game-filters") gameResults();
    if (target.name === "area" && main.querySelector("#court-results")) {
        filters.area = target.value;
        renderResults();
    }
    if (target.name === "date" && main.querySelector("#court-results")) {
        filters.date = validDate(target.value);
        renderResults();
    }
    if (
        main.querySelector("#booking-slots") &&
        ["duration", "date"].includes(target.name)
    ) {
        if (target.name === "date") selection.date = validDate(target.value);
        else selection.duration = Number(target.value);
        renderSlots();
    }
});

document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-action]");
    if (!button) return;
    const { action, value } = button.dataset;
    if (handlePlayerAction(action, value, button)) return;
    switch (action) {
        case "close":
            dialog.close();
            break;
        case "filters":
            showFilters();
            break;
        case "sport":
        case "available":
            if (action === "sport") {
                filters.sport = value;
                main.querySelectorAll('[data-action="sport"]').forEach((b) =>
                    b.setAttribute("aria-pressed", String(b === button)),
                );
            } else {
                filters.available = !filters.available;
                button.setAttribute("aria-pressed", String(filters.available));
            }
            renderResults();
            break;
        case "reset":
            filters = defaultFilters(
                new URLSearchParams({ date: filters.date }),
            );
            if (dialog.open) dialog.close();
            updateCourtControls();
            renderResults();
            break;
        case "gallery":
            showDialog(
                "Court image gallery",
                image(
                    selection.court.images[Number(value)],
                    selection.court.facility,
                    'class="gallery"',
                ),
            );
            break;
        case "calendar-day":
            selection.date = value;
            main.querySelector("[name=date]").value = value;
            renderSlots();
            break;
        case "map":
            updateMap(getCourt(value));
            break;
        case "slot":
            selection.hour = Number(value);
            main.querySelectorAll('[data-action="slot"]').forEach((b) =>
                b.setAttribute("aria-pressed", String(b === button)),
            );
            main.querySelector('[data-action="review"]').disabled = false;
            break;
        case "review":
            if (!requirePlayer()) break;
            showDialog(
                "Review booking",
                `<div class="stack">${image(selection.court.images[0], selection.court.facility, 'class="gallery"')}<strong>${escape(selection.court.facility)}</strong>
        <span>${formatDate(selection.date)} · ${formatHour(selection.hour)} · ${selection.duration} hr</span>
        <strong>${jod(selection.court.pricePerHour * selection.duration)}</strong>
        <p class="muted">Demo reservation. No payment is collected.</p><button data-action="confirm">Confirm booking</button></div>`,
            );
            break;
        case "confirm": {
            if (
                !state.signedIn ||
                !availableStarts(selection.court).includes(selection.hour)
            ) {
                dialog.close();
                renderSlots();
                notify("This time is no longer available.");
                break;
            }
            const ref =
                "CB-" +
                Array.from(crypto.getRandomValues(new Uint8Array(4)), (n) =>
                    n.toString(16).padStart(2, "0"),
                )
                    .join("")
                    .toUpperCase();
            state.bookings.unshift({
                ref,
                courtId: selection.court.id,
                date: selection.date,
                startHour: selection.hour,
                duration: selection.duration,
                total: selection.court.pricePerHour * selection.duration,
                status: "upcoming",
            });
            showDialog(
                "Booking confirmed",
                `<p>Your reservation reference is <strong>${ref}</strong>.</p>${link("bookings", "View my bookings")}`,
            );
            renderSlots();
            addAlert(
                "booking_confirmed",
                "Demo booking confirmed",
                ref,
                "bookings",
            );
            save();
            break;
        }
        case "cancel":
            showDialog(
                "Cancel booking?",
                `<p>Cancel reservation <strong>${escape(value)}</strong>?</p><div class="row"><button class="outline" data-action="account">Keep booking</button><button data-action="cancel-confirm" data-value="${escape(value)}">Cancel booking</button></div>`,
            );
            break;
        case "cancel-confirm": {
            const booking = state.bookings.find(
                (b) => b.ref === value && b.status === "upcoming",
            );
            if (booking && state.signedIn) {
                booking.status = "cancelled";
                for (const game of state.games)
                    if (game.bookingRef === value)
                        game.bookingStatus = "proposed";
            }
            if (main.querySelector("#booking-slots")) renderSlots();
            if (main.querySelector("#court-results")) renderResults();
            dialog.close();
            if (booking && state.signedIn) {
                addAlert(
                    "booking_cancelled",
                    "Demo booking cancelled",
                    value,
                    "bookings",
                );
                save();
                if (currentPage().startsWith("bookings")) render();
                notify("Booking cancelled.");
            }
            break;
        }
        case "join":
            showGameConfirmation(value);
            break;
        case "join-commit": {
            const game = state.games.find((g) => g.id === value);
            if (!game || game.status === "cancelled" || !state.signedIn) break;
            dialog.close();
            updateGameMembership(game);
            main.innerHTML = localizedMarkup(gameDetails(game));
            save();
            break;
        }
    }
});

document.addEventListener("submit", (event) => {
    const form = event.target;
    const data = new FormData(form);
    if (handlePlayerForm(form, data)) {
        event.preventDefault();
        return;
    }
    if (form.id === "map-filters" || form.id === "game-filters") {
        event.preventDefault();
        if (form.id === "map-filters") renderNearby();
        else gameResults();
    }
    if (form.id === "advanced") {
        event.preventDefault();
        const from = Number(data.get("from"));
        const to = Number(data.get("to"));
        if (from >= to) {
            const field = form.elements.to;
            field.setCustomValidity(uiText("Choose an end time after the start time."));
            field.reportValidity();
            field.setCustomValidity("");
            return;
        }
        Object.assign(filters, {
            from,
            to,
            price: Number(data.get("price")),
            setting: data.get("setting"),
            amenities: data.getAll("amenity"),
        });
        dialog.close();
        renderResults();
    }
});

render();

setInterval(refreshOffers, 1000);
