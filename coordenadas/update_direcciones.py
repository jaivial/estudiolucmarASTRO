import json
import re

# Define patterns to remove
remove_patterns_list = [
    r'Pl:[^\s]*',  # Matches 'Pl:XX' where XX is anything (non-whitespace characters)
    r'Pt:[^\s]*',  # Matches 'Pt:XX' where XX is anything (non-whitespace characters)
    r'Es:[^\s]*',  # Matches 'Es:X' where X is anything (non-whitespace characters)
    r'Bl:[^\s]*'   # Matches 'Bl:X' where X is anything (non-whitespace characters)
]

# Define replacements
replacements = {
    'AV': 'Avinguda',
    'PZ': 'Plaza',
    'CL': 'Calle',
    'GR': 'Grupo',
    'SD': 'Sendas',
    'CM': 'Camino',
    'RD': 'Ronda',
    'BO': 'Barrio',
    'CJ': 'Calleja'  # Added replacement for 'CJ' to 'Calleja'
}

# Read addresses from direcciones.json
with open('direcciones.json', 'r') as file:
    direcciones = json.load(file)

# Function to remove patterns from a string
def remove_patterns(address, patterns):
    for pattern in patterns:
        address = re.sub(pattern, '', address)
    return address.strip()

# Function to replace patterns in a string
def replace_patterns(address, replacements):
    for old, new in replacements.items():
        address = address.replace(old, new)
    return address

# Process each address
updated_direcciones = []
for address in direcciones:
    address = remove_patterns(address, remove_patterns_list)
    address = replace_patterns(address, replacements)
    updated_direcciones.append(address)

# Save updated addresses to direcciones2.json
with open('direcciones2.json', 'w') as file:
    json.dump(updated_direcciones, file, ensure_ascii=False, indent=4)

print("Updated addresses have been saved to 'direcciones2.json'")
