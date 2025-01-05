// Data for Malaysia's states and cities (no postal codes assigned)
const stateCityData = {
    "Johor": [
      "Johor Bahru", "Mersing", "Kota Tinggi", "Tebrau", "Pasir Gudang", "Bukit Indah", "Skudai", "Kluang", "Batu Pahat",
      "Muar", "Ulu Tiram", "Senai", "Segamat", "Kulai", "Pontian Kechil", "Tangkak", "Bukit Bakri", "Yong Peng", "Pekan Nenas", "Labis", "Simpang Renggam",
      "Parit Raja", "Kelapa Sawit", "Buloh Kasap", "Chaah"
    ],
    "Kedah": [
      "Alor Setar", "Sungai Petani", "Kulim", "Jitra", "Baling", "Pendang", "Langkawi", "Yan", "Sik", "Kuala Nerang", "Pokok Sena", "Bandar Baharu"
    ],
    "Kelantan": [
      "Kota Bharu", "Tumpat", "Kuala Krai", "Gua Musang", "Pasir Mas", "Kadok", "Wakaf Bharu", "Peringat", "Tanah Merah", "Pangkalan Kubor"
    ],
    "Selangor": [
      "Shah Alam", "Petaling Jaya", "Subang Jaya", "Ampang Jaya", "Cheras", "Kajang", "Selayang", "Rawang", "Taman Greenwood", "Semenyih", "Banting", "Balakong",
      "Gombak Setia", "Kuala Selangor", "Serendah", "Bukit Beruntung", "Sungai Besar", "Batu Arang", "Tanjung Sepat", "Kuala Kubu Baharu", "Batang Berjuntai", 
      "Bandar Baru Salak Tinggi", "Sekinchan", "Sabak", "Tanjung Karang", "Beranang", "Sungai Pelek"
    ],
    "Perlis": [
      "Kangar", "Arau", "Padang Besar"
    ],
    "Wilayah Persekutuan": [
      "Kuala Lumpur", "Labuan", "Putrajaya"
    ],
    "Sarawak": [
      "Kuching", "Miri", "Sibu", "Bintulu", "Limbang", "Sarikei", "Sri Aman"
    ],
    "Sabah": [
      "Kota Kinabalu", "Sandakan", "Lahad Datu", "Kenigau", "Putatan", "Donggongan", "Semporna", "Kudat", "Kunak", "Papar", "Ranau", "Beaufort", "Kota Belud"
    ],
    "Terengganu": [
      "Kuala Terengganu", "Chukai", "Dungun", "Kerteh", "Kuala Berang", "Marang", "Paka", "Jerteh"
    ],
    "Perak": [
      "Ipoh", "Taiping", "Sitiawan", "Simpang Empat", "Teluk Intan", "Batu Gajah", "Lumut", "Kampung Koh", "Kuala Kangsar", "Sungai Siput", "Tapah",
      "Bidor", "Parit Buntar", "Ayer Tawar", "Bagan Serai", "Tanjung Malim", "Pantai Remis", "Kampar", "Bagan Datoh", "Kampung Gajah"
    ],
    "Negeri Sembilan": [
      "Seremban", "Port Dickson", "Nilai", "Bahau", "Tampin", "Kuala Pilah"
    ],
    "Pahang": [
      "Kuantan", "Temerloh", "Bentong", "Mentakab", "Raub", "Jerantut", "Pekan", "Kuala Lipis", "Bandar Jengka", "Bukit Tinggi"
    ],
    "Melaka": [
      "Bandaraya Melaka", "Bukit Baru", "Ayer Keroh", "Klebang", "Masjid Tanah", "Sungai Udang", "Batu Berendam", "Alor Gajah", "Bukit Rembai", 
      "Ayer Molek", "Bemban", "Kuala Sungai Baru", "Pulau Sebang" 
    ],
    "Penang": [
      "Georgetown", "Bukit Mertajam", "Sungai Ara", "Gelugor", "Air Itam", "Butterworth", "Perai", "Nibong Tebal", "Permatang Pauh", "Tanjung Tokoh",
      "Tanjung Tokong", "Kepala Batas", "Tanjung  Bungah"
    ]
    
    // Add other states with their respective cities here...
  };
  
  // Function to update city options based on selected state
  function updateCityOptions() {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('city');
  
    // Get the selected state
    const selectedState = stateSelect.value;
  
    // Clear existing city options
    citySelect.innerHTML = '<option value="" disabled selected>Select your city</option>';
  
    // Check if data exists for the selected state
    if (selectedState && stateCityData[selectedState]) {
      const cities = stateCityData[selectedState];
  
      // Populate the city dropdown based on the selected state
      cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        citySelect.appendChild(option);
      });
    }
  }
  
  // Add event listener for state selection to update cities
  document.getElementById('state').addEventListener('change', updateCityOptions);
  
  // Initialize city options on page load
  document.addEventListener('DOMContentLoaded', updateCityOptions);