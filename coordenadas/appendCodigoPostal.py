import json

# Read addresses from direcciones3.json
with open('direcciones3.json', 'r') as file:
    direcciones = json.load(file)

# Append the text to each address
updated_direcciones = [address + " Catarroja 46470 València" for address in direcciones]

# Save the updated addresses to direcciones4.json
with open('direcciones4.json', 'w') as file:
    json.dump(updated_direcciones, file, ensure_ascii=False, indent=4)

print("Updated addresses have been saved to 'direcciones4.json'")
