/* ══════════════════════════════════════════════════════
   GOOGLE MAPS INTEGRATION — FAV Directory
   ══════════════════════════════════════════════════════ */

// Coordonnées par défaut des pays
const countryCoords = {
  'France': { lat: 48.8566, lng: 2.3522, name: 'Paris' },
  'Albania': { lat: 41.3275, lng: 19.8187, name: 'Tirana' },
  'Vietnam': { lat: 21.0285, lng: 105.8542, name: 'Hanoï' },
  'US': { lat: 40.7128, lng: -74.0060, name: 'New York' }
};

let map;
let markers = [];

function initMap() {
  // Centre sur l'Europe/Asie
  const center = { lat: 35, lng: 50 };

  map = new google.maps.Map(document.getElementById('directoryMap'), {
    zoom: 4,
    center: center,
    styles: [
      {
        "featureType": "all",
        "elementType": "labels.text.fill",
        "stylers": [{"color": "#212121"}]
      }
    ]
  });

  // Ajouter des marqueurs pour chaque utilisateur (mock)
  const mockUsers = [
    { username: "Sophie", country: "France", bio: "Voyageuse" },
    { username: "Artan", country: "Albania", bio: "Digital nomad" },
    { username: "Linh", country: "Vietnam", bio: "Foodie" },
    { username: "Mike", country: "US", bio: "Explorer" }
  ];

  mockUsers.forEach(user => {
    const coords = countryCoords[user.country];
    if (coords) {
      addMarker(coords, user);
    }
  });
}

function addMarker(coords, user) {
  const marker = new google.maps.Marker({
    position: { lat: coords.lat, lng: coords.lng },
    map: map,
    title: user.username,
    icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
  });

  const infoWindow = new google.maps.InfoWindow({
    content: `
      <div style="padding: 10px; font-family: Inter, sans-serif;">
        <strong>${user.username}</strong><br>
        📍 ${coords.name}, ${user.country}<br>
        <em>${user.bio}</em>
      </div>
    `
  });

  marker.addListener('click', () => {
    infoWindow.open(map, marker);
  });

  markers.push(marker);
}