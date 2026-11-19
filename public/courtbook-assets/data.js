/**
 * Sample data for the CourtBook design prototype.
 * In production every value here comes from the Laravel API / MySQL.
 * Facilities, prices and availability below are placeholders, not real inventory.
 */

                                                                                

const SPORTS                                                = [
  { id: 'football', label: 'Football', image: 'images/football-indoor.png' },
  { id: 'basketball', label: 'Basketball', image: 'images/basketball.png' },
  { id: 'tennis', label: 'Tennis', image: 'images/tennis.png' },
  { id: 'padel', label: 'Padel', image: 'images/padel.png' },
  { id: 'badminton', label: 'Badminton', image: 'images/badminton.png' },
]

const sportLabel = (s       ) => SPORTS.find((x) => x.id === s)?.label ?? s

const AMENITIES = [
  'Parking',
  'Changing rooms',
  'Showers',
  'Floodlights',
  'Equipment rental',
  'Café',
  'Air conditioning',
  'Water station',
]         

                     
            
                  
              
              
              
                 
             
             
                
                 
                      
                 
                     
                  
                     
                                                              
                   
                  
                   
 

const COURTS_ALL          = [
  {
    id: 'khalda-arena-pitch-1',
    facility: 'Khalda Sports Arena',
    name: 'Pitch 1 · 5-a-side',
    sport: 'football',
    area: 'Khalda',
    address: 'Wasfi Al Tal St, Khalda, Amman',
    lat: 31.9958,
    lng: 35.8352,
    rating: 4.8,
    reviews: 214,
    pricePerHour: 28,
    indoor: false,
    amenities: ['Parking', 'Changing rooms', 'Showers', 'Floodlights', 'Water station'],
    images: ['images/football-indoor.png', 'images/hero-football.png', 'images/players.png'],
    description:
      'A full-size 5-a-side artificial turf pitch with professional floodlights, perfect for evening games. Bibs and balls are available at reception.',
    owner: { name: 'Khalda Sports Arena LLC', since: 2019, responseTime: 'Replies within 1 hour' },
    approved: true,
    openHour: 8,
    closeHour: 24,
  },
  {
    id: 'abdoun-padel-club-c2',
    facility: 'Abdoun Padel Club',
    name: 'Court 2 · Panoramic',
    sport: 'padel',
    area: 'Abdoun',
    address: 'Cairo St, Abdoun, Amman',
    lat: 31.9452,
    lng: 35.8848,
    rating: 4.9,
    reviews: 168,
    pricePerHour: 20,
    indoor: true,
    amenities: ['Parking', 'Changing rooms', 'Café', 'Equipment rental', 'Air conditioning'],
    images: ['images/padel.png', 'images/players.png', 'images/tennis.png'],
    description:
      'Panoramic glass padel court with WPT-grade turf. Indoor and climate controlled for year-round play. Racket rental available.',
    owner: { name: 'Abdoun Padel Club', since: 2021, responseTime: 'Replies within 30 min' },
    approved: true,
    openHour: 7,
    closeHour: 23,
  },
  {
    id: 'sweifieh-tennis-centre-c1',
    facility: 'Sweifieh Tennis Centre',
    name: 'Hard Court 1',
    sport: 'tennis',
    area: 'Sweifieh',
    address: 'Ali Nasouh Al Taher St, Sweifieh, Amman',
    lat: 31.9561,
    lng: 35.8604,
    rating: 4.6,
    reviews: 92,
    pricePerHour: 16,
    indoor: false,
    amenities: ['Parking', 'Floodlights', 'Water station', 'Equipment rental'],
    images: ['images/tennis.png', 'images/padel.png'],
    description:
      'Well-maintained outdoor hard court with night lighting. Ideal for singles and doubles practice sessions.',
    owner: { name: 'Sweifieh Tennis Centre', since: 2015, responseTime: 'Replies within 2 hours' },
    approved: true,
    openHour: 7,
    closeHour: 22,
  },
  {
    id: 'shmeisani-hoops-main',
    facility: 'Shmeisani Hoops Hall',
    name: 'Main Court',
    sport: 'basketball',
    area: 'Shmeisani',
    address: 'Abdel Hamid Sharaf St, Shmeisani, Amman',
    lat: 31.9712,
    lng: 35.8998,
    rating: 4.7,
    reviews: 131,
    pricePerHour: 25,
    indoor: true,
    amenities: ['Changing rooms', 'Showers', 'Air conditioning', 'Water station'],
    images: ['images/basketball.png', 'images/players.png'],
    description:
      'Full-size indoor hardwood basketball court with electronic scoreboard. Suitable for full-court 5v5 games.',
    owner: { name: 'Shmeisani Hoops Hall', since: 2018, responseTime: 'Replies within 1 hour' },
    approved: true,
    openHour: 9,
    closeHour: 23,
  },
  {
    id: 'rabieh-shuttle-c3',
    facility: 'Al Rabieh Shuttle Club',
    name: 'Court 3',
    sport: 'badminton',
    area: 'Al Rabieh',
    address: 'Mecca St, Al Rabieh, Amman',
    lat: 31.9822,
    lng: 35.8797,
    rating: 4.5,
    reviews: 58,
    pricePerHour: 10,
    indoor: true,
    amenities: ['Parking', 'Air conditioning', 'Equipment rental'],
    images: ['images/badminton.png', 'images/players.png'],
    description: 'Tournament-standard badminton court with non-slip mat flooring and glare-free lighting.',
    owner: { name: 'Al Rabieh Shuttle Club', since: 2020, responseTime: 'Replies within 3 hours' },
    approved: true,
    openHour: 8,
    closeHour: 22,
  },
  {
    id: 'dabouq-football-park-p2',
    facility: 'Dabouq Football Park',
    name: 'Pitch 2 · 7-a-side',
    sport: 'football',
    area: 'Dabouq',
    address: 'King Hussein Park Rd, Dabouq, Amman',
    lat: 32.0011,
    lng: 35.8102,
    rating: 4.7,
    reviews: 176,
    pricePerHour: 35,
    indoor: false,
    amenities: ['Parking', 'Changing rooms', 'Floodlights', 'Café'],
    images: ['images/hero-football.png', 'images/football-indoor.png'],
    description: 'Spacious 7-a-side pitch with premium turf and seating for spectators.',
    owner: { name: 'Dabouq Football Park', since: 2017, responseTime: 'Replies within 1 hour' },
    approved: true,
    openHour: 8,
    closeHour: 24,
  },
  {
    id: 'tla-ali-padel-c1',
    facility: 'Tla Al Ali Padel Hub',
    name: 'Court 1',
    sport: 'padel',
    area: "Tla' Al Ali",
    address: 'Al Madina Al Munawara St, Tla Al Ali, Amman',
    lat: 31.9901,
    lng: 35.8551,
    rating: 4.4,
    reviews: 47,
    pricePerHour: 18,
    indoor: false,
    amenities: ['Parking', 'Floodlights', 'Equipment rental'],
    images: ['images/padel.png', 'images/tennis.png'],
    description: 'Outdoor padel court with LED floodlights. Great value for evening doubles.',
    owner: { name: 'Tla Al Ali Padel Hub', since: 2022, responseTime: 'Replies within 2 hours' },
    approved: true,
    openHour: 8,
    closeHour: 23,
  },
  {
    id: 'jabal-amman-community-court',
    facility: 'Jabal Amman Community Centre',
    name: 'Outdoor Court',
    sport: 'basketball',
    area: 'Jabal Amman',
    address: 'Rainbow St, Jabal Amman, Amman',
    lat: 31.9519,
    lng: 35.9121,
    rating: 4.3,
    reviews: 39,
    pricePerHour: 15,
    indoor: false,
    amenities: ['Floodlights', 'Water station'],
    images: ['images/basketball.png'],
    description: 'Community outdoor court in the heart of Jabal Amman. Casual games and training.',
    owner: { name: 'Jabal Amman Community Centre', since: 2016, responseTime: 'Replies within 4 hours' },
    approved: true,
    openHour: 9,
    closeHour: 22,
  },
  {
    id: 'pending-facility',
    facility: 'Pending Review Facility',
    name: 'Court A',
    sport: 'tennis',
    area: 'Khalda',
    address: 'Khalda, Amman',
    lat: 31.999,
    lng: 35.84,
    rating: 0,
    reviews: 0,
    pricePerHour: 0,
    indoor: false,
    amenities: [],
    images: ['images/tennis.png'],
    description: '',
    owner: { name: 'Pending', since: 2026, responseTime: '' },
    approved: false,
    openHour: 8,
    closeHour: 22,
  },
]

/** Only approved facilities are ever exposed publicly. */
const COURTS = COURTS_ALL.filter((c) => c.approved)

const getCourt = (id        ) => COURTS.find((c) => c.id === id)

/** Fixed demo "today" so server and client render identical dates. */
const DEMO_TODAY = new Date(2026, 10, 18, 9, 0, 0)

const addDays = (d      , n        ) => {
  const x = new Date(d)
  x.setDate(x.getDate() + n)
  return x
}

const toISODate = (d      ) =>
  `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`

const fromISODate = (s        ) => {
  const [y, m, d] = s.split('-').map(Number)
  return new Date(y, m - 1, d)
}

const formatDate = (iso        , opts                             = { weekday: 'short', day: 'numeric', month: 'short' }) =>
  fromISODate(iso).toLocaleDateString(uiLocale, opts)

const formatHour = (h        ) => {
  if (arabicUI) return new Date(2000, 0, 1, h % 24).toLocaleTimeString(uiLocale, { hour: 'numeric', minute: '2-digit', hour12: true })
  const hr = h % 24
  const suffix = hr >= 12 ? 'PM' : 'AM'
  const twelve = hr % 12 === 0 ? 12 : hr % 12
  return `${twelve}:00 ${suffix}`
}

const jod = (n        ) => arabicUI ? uiNumber.format(n) + " د.أ" : `${n % 1 === 0 ? n : n.toFixed(2)} JOD`

function hash(str        ) {
  let h = 2166136261
  for (let i = 0; i < str.length; i++) {
    h ^= str.charCodeAt(i)
    h = Math.imul(h, 16777619)
  }
  return h >>> 0
}

                                                       

/**
 * Deterministic placeholder availability. Production replaces this with
 * GET /api/courts/{id}/availability?date=YYYY-MM-DD.
 */
function getSlots(courtId        , isoDate        )                                       {
  const court = COURTS_ALL.find((c) => c.id === courtId)
  if (!court) return []
  const isToday = isoDate === toISODate(DEMO_TODAY)
  const slots                                       = []
  for (let h = court.openHour; h < court.closeHour; h++) {
    if (isToday && h <= DEMO_TODAY.getHours()) {
      slots.push({ hour: h, state: 'past' })
      continue
    }
    const busy = hash(`${courtId}-${isoDate}-${h}`) % 100 < (h >= 18 && h <= 21 ? 55 : 28)
    slots.push({ hour: h, state: busy ? 'booked' : 'available' })
  }
  return slots
}

const isRangeAvailable = (courtId        , isoDate        , start        , duration        ) => {
  const slots = getSlots(courtId, isoDate)
  for (let h = start; h < start + duration; h++) {
    const s = slots.find((x) => x.hour === h)
    if (!s || s.state !== 'available') return false
  }
  return true
}

const availableCount = (courtId        , isoDate        ) =>
  getSlots(courtId, isoDate).filter((s) => s.state === 'available').length

/** Distance in km using the haversine formula. */
function distanceKm(a                              , b                              ) {
  const R = 6371
  const dLat = ((b.lat - a.lat) * Math.PI) / 180
  const dLng = ((b.lng - a.lng) * Math.PI) / 180
  const s =
    Math.sin(dLat / 2) ** 2 +
    Math.cos((a.lat * Math.PI) / 180) * Math.cos((b.lat * Math.PI) / 180) * Math.sin(dLng / 2) ** 2
  return 2 * R * Math.asin(Math.sqrt(s))
}

const AMMAN_CENTER = { lat: 31.9632, lng: 35.8735 }

const AREAS = Array.from(new Set(COURTS.map((c) => c.area)))

/* ---------------- Players & games ---------------- */

                                                                                  

const COLORS = ['#16A34A', '#0EA5E9', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1']

const mk = (id        , name        , i        )         => ({
  id,
  name,
  initials: name
    .split(' ')
    .map((p) => p[0])
    .join('')
    .slice(0, 2),
  color: COLORS[i % COLORS.length],
})

const PLAYERS           = [
  'Omar Haddad',
  'Lina Khoury',
  'Yazan Saleh',
  'Rania Odeh',
  'Karim Nasser',
  'Dana Mansour',
  'Faris Qasem',
  'Sara Azar',
  'Hamza Toukan',
  'Noor Jaber',
  'Tariq Shami',
  'Maya Rizk',
  'Zaid Hourani',
  'Leen Barakat',
].map((n, i) => mk(`p${i + 1}`, n, i))

const CURRENT_USER = {
  id: 'me',
  name: 'Ahmad Khalil',
  initials: 'AK',
  email: 'ahmad.khalil@example.com',
  phone: '+962 7 9000 0000',
  preferredSports: ['football', 'padel']           ,
  memberSince: 'March 2026',
  color: '#166534',
}

                                                                                

                    
            
               
              
                 
              
                   
                  
                     
                    
                     
                       
                        
                   
                     
                                                                                    
                                         
                                       
 

const iso = (n        ) => toISODate(addDays(DEMO_TODAY, n))

const GAMES         = [
  {
    id: 'friday-night-football',
    title: 'Friday Night Football',
    sport: 'football',
    courtId: 'khalda-arena-pitch-1',
    date: iso(2),
    startHour: 20,
    duration: 1,
    organizerId: 'p1',
    maxPlayers: 10,
    playerIds: ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8'],
    waitlistIds: [],
    pricePerPlayer: 3,
    skill: 'Intermediate',
    description: 'Friendly 5v5 under the lights. Bring a dark and a light shirt. We split teams on arrival.',
    bookingStatus: 'confirmed',
    status: 'open',
  },
  {
    id: 'sunday-padel-doubles',
    title: 'Sunday Padel Doubles',
    sport: 'padel',
    courtId: 'abdoun-padel-club-c2',
    date: iso(4),
    startHour: 18,
    duration: 2,
    organizerId: 'p2',
    maxPlayers: 4,
    playerIds: ['p2', 'p8', 'p10'],
    waitlistIds: [],
    pricePerPlayer: 10,
    skill: 'Intermediate',
    description: 'Looking for a fourth player for competitive doubles. Rackets available at the club.',
    bookingStatus: 'confirmed',
    status: 'open',
  },
  {
    id: 'full-court-hoops',
    title: 'Full Court Hoops 5v5',
    sport: 'basketball',
    courtId: 'shmeisani-hoops-main',
    date: iso(1),
    startHour: 19,
    duration: 2,
    organizerId: 'p5',
    maxPlayers: 10,
    playerIds: ['p5', 'p1', 'p3', 'p7', 'p9', 'p11', 'p12', 'p13', 'p14', 'p6'],
    waitlistIds: ['p4', 'p10'],
    pricePerPlayer: 5,
    skill: 'Advanced',
    description: 'Fast-paced full court games, first to 21. Game is full — join the waiting list for a spot.',
    bookingStatus: 'confirmed',
    status: 'full',
  },
  {
    id: 'morning-tennis-rally',
    title: 'Morning Tennis Rally',
    sport: 'tennis',
    courtId: 'sweifieh-tennis-centre-c1',
    date: iso(3),
    startHour: 8,
    duration: 1,
    organizerId: 'p4',
    maxPlayers: 2,
    playerIds: ['p4'],
    waitlistIds: [],
    pricePerPlayer: 8,
    skill: 'Beginner',
    description: 'Relaxed rally session for beginners. Happy to play some practice games.',
    bookingStatus: 'proposed',
    status: 'open',
  },
  {
    id: 'badminton-social',
    title: 'Badminton Social Night',
    sport: 'badminton',
    courtId: 'rabieh-shuttle-c3',
    date: iso(5),
    startHour: 19,
    duration: 2,
    organizerId: 'p6',
    maxPlayers: 4,
    playerIds: ['p6', 'p12'],
    waitlistIds: [],
    pricePerPlayer: 5,
    skill: 'All Levels',
    description: 'Rotating doubles, all levels welcome. Shuttles provided.',
    bookingStatus: 'confirmed',
    status: 'open',
  },
  {
    id: 'saturday-7s',
    title: 'Saturday 7-a-side',
    sport: 'football',
    courtId: 'dabouq-football-park-p2',
    date: iso(3),
    startHour: 17,
    duration: 1,
    organizerId: 'p9',
    maxPlayers: 14,
    playerIds: ['p9', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p10', 'p11', 'p12', 'p13', 'p14'],
    waitlistIds: ['p2'],
    pricePerPlayer: 2.5,
    skill: 'All Levels',
    description: 'Weekly 7-a-side. Bibs provided. Full for now — the waiting list moves fast.',
    bookingStatus: 'confirmed',
    status: 'full',
  },
]

const getGame = (id        ) => GAMES.find((g) => g.id === id)
const getPlayer = (id        ) => (id === 'me' ? { ...CURRENT_USER } : PLAYERS.find((p) => p.id === id))

/* ---------------- Bookings, subscriptions, notifications ---------------- */

                       
             
                 
              
                   
                  
               
                                                
 

const INITIAL_BOOKINGS            = [
  { ref: 'CB-7Q4K2M', courtId: 'abdoun-padel-club-c2', date: iso(1), startHour: 18, duration: 1, total: 20, status: 'upcoming' },
  { ref: 'CB-3H9W1A', courtId: 'khalda-arena-pitch-1', date: iso(6), startHour: 21, duration: 1, total: 28, status: 'upcoming' },
  { ref: 'CB-8D2P5X', courtId: 'sweifieh-tennis-centre-c1', date: iso(-4), startHour: 9, duration: 1, total: 16, status: 'completed' },
  { ref: 'CB-1Z6R8T', courtId: 'shmeisani-hoops-main', date: iso(-10), startHour: 20, duration: 2, total: 50, status: 'completed' },
  { ref: 'CB-5N7V3C', courtId: 'rabieh-shuttle-c3', date: iso(-2), startHour: 17, duration: 1, total: 10, status: 'cancelled' },
]

                            
            
                 
                
                   
                  
                   
                      
                       
                      
                                            
                 
 

const INITIAL_SUBSCRIPTIONS                 = [
  {
    id: 'SUB-2041',
    courtId: 'abdoun-padel-club-c2',
    days: [1, 3],
    startHour: 19,
    duration: 1,
    startDate: '2026-11-02',
    monthlyTotal: 162,
    sessionsTotal: 9,
    sessionsUsed: 4,
    status: 'active',
    renews: true,
  },
  {
    id: 'SUB-1877',
    courtId: 'khalda-arena-pitch-1',
    days: [5],
    startHour: 20,
    duration: 1,
    startDate: '2026-09-01',
    monthlyTotal: 106.4,
    sessionsTotal: 4,
    sessionsUsed: 4,
    status: 'expired',
    renews: false,
  },
]

                              
                       
                       
                 
                   
                    
                           

                               
            
                        
               
              
              
               
               
 

const INITIAL_NOTIFICATIONS                    = [
  {
    id: 'n1',
    kind: 'waitlist_spot',
    title: 'A spot opened up',
    body: 'Full Court Hoops 5v5 has a spot for you. Confirm within 15 minutes.',
    time: '2 min ago',
    read: false,
    href: '/games/waitlist',
  },
  {
    id: 'n2',
    kind: 'booking_confirmed',
    title: 'Booking confirmed',
    body: 'Abdoun Padel Club · Court 2, tomorrow at 6:00 PM. Ref CB-7Q4K2M.',
    time: '1 hr ago',
    read: false,
    href: '/bookings',
  },
  {
    id: 'n3',
    kind: 'game_invite',
    title: 'Game invitation',
    body: 'Lina invited you to Sunday Padel Doubles.',
    time: '3 hr ago',
    read: false,
    href: '/games/sunday-padel-doubles',
  },
  {
    id: 'n4',
    kind: 'subscription_reminder',
    title: 'Subscription renews soon',
    body: 'Your padel subscription renews on 2 Dec. 5 sessions remaining this month.',
    time: 'Yesterday',
    read: true,
    href: '/subscriptions/mine',
  },
  {
    id: 'n5',
    kind: 'invite_expired',
    title: 'Invitation expired',
    body: 'Your waiting-list offer for Saturday 7-a-side expired and passed to the next player.',
    time: '2 days ago',
    read: true,
    href: '/bookings',
  },
  {
    id: 'n6',
    kind: 'booking_cancelled',
    title: 'Booking cancelled',
    body: 'Al Rabieh Shuttle Club · Court 3 was cancelled. Refund issued.',
    time: '2 days ago',
    read: true,
    href: '/bookings',
  },
]
