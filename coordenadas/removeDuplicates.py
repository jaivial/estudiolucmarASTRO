import json

# Read addresses from direcciones2.json
with open('direcciones2.json', 'r') as file:
    direcciones = json.load(file)

# Remove duplicate addresses by converting the list to a set, then back to a list
unique_direcciones = list(set(direcciones))

# Save unique addresses to direcciones3.json
with open('direcciones3.json', 'w') as file:
    json.dump(unique_direcciones, file, ensure_ascii=False, indent=4)

print("Unique addresses have been saved to 'direcciones3.json'")
