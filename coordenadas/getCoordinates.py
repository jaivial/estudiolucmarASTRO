import json
from geopy.geocoders import Nominatim
from geopy.exc import GeocoderTimedOut

# Load addresses from JSON file
with open('direcciones4.json', 'r', encoding='utf-8') as f:
    addresses = json.load(f)

# Initialize geocoder
geolocator = Nominatim(user_agent="geoapiExercises")

# Lists to store results
success_results = []
failure_results = []

# Function to geocode address and return coordinates
def geocode_address(address):
    try:
        location = geolocator.geocode(address, timeout=10)
        if location:
            coordinates = (location.latitude, location.longitude)
            success_results.append({"address": address, "coordinates": coordinates})
        else:
            failure_results.append({"address": address, "error": "Not found"})
    except GeocoderTimedOut:
        failure_results.append({"address": address, "error": "Geocoding timed out"})
    except Exception as e:
        failure_results.append({"address": address, "error": str(e)})

# Process each address and collect results
for address in addresses:
    geocode_address(address)

# Write results to JSON files
with open('success.json', 'w', encoding='utf-8') as success_file:
    json.dump(success_results, success_file, ensure_ascii=False, indent=4)

with open('failure.json', 'w', encoding='utf-8') as failure_file:
    json.dump(failure_results, failure_file, ensure_ascii=False, indent=4)

# Print summary
print(f"Successfully geocoded addresses saved to success.json")
print(f"Failed to geocode addresses saved to failure.json")
