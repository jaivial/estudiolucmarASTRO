import json
import re

# Mapping of words to substitute
word_mapping = {
    "Avinguda": "AV",
    "Plaza": "PZ",
    "Calle": "CL",
    "Grupo": "GR",
    "Sendas": "SD",
    "Camino": "CM",
    "Ronda": "RD",
    "Barrio": "BO",
    "Calleja": "CJ"
}

def substitute_and_delete(data):
    # Function to recursively substitute words and delete specific string
    if isinstance(data, dict):
        for key, value in data.items():
            if isinstance(value, str):
                # Delete the specific string "Catarroja 46470 València" and preserve spaces
                value = re.sub(r'\bCatarroja\s+46470\s+València\b', '', value)

                # Substitute words if the value is a string
                for original, substitute in word_mapping.items():
                    value = value.replace(original, substitute)

                data[key] = value.strip()  # Strip leading/trailing spaces
            else:
                # Recursively process nested dictionaries or lists
                substitute_and_delete(value)
    elif isinstance(data, list):
        # Recursively process each element in a list
        for item in data:
            substitute_and_delete(item)

def main():
    input_file = 'bounding_boxes.json'
    output_file = 'updated_bounding_boxes.json'

    # Read the original JSON file
    with open(input_file, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # Perform substitutions and delete specific string
    substitute_and_delete(data)

    # Write the updated data to a new JSON file
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

    print(f"Updated data has been written to {output_file}")

if __name__ == "__main__":
    main()
