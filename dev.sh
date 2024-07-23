#!/bin/bash

# Function to process files
process_files() {
    local dir=$1

    # Find all files in the directory and its subdirectories
    find "$dir" -type f | while read -r file; do
        echo "Checking file: $file"
        # Check if the file contains the string we are looking for
        if grep -q 'https://estudiolucmar.com/' "$file"; then
            echo "Found matching string in: $file"
            # Replace the string in the file
            sed -i '' 's|https://estudiolucmar.com/|http://localhost:8000/|g' "$file"
            echo "Processed: $file"
        fi
    done
}

# Function to update database connection details for development
update_dev_db_connection() {
    local db_file="./public/backend/db_Connection/db_Connection.php"

    if [ -f "$db_file" ]; then
        echo "Updating development database connection details in $db_file"
        sed -i '' 's|\$host = "localhost";|\$host = "127.0.0.1";|g' "$db_file"
        sed -i '' 's|\$port = "3306";|\$port = "3308";|g' "$db_file"
        sed -i '' 's|\$user = "u212050690_estudiolucmar";|\$user = "root";|g' "$db_file"
        sed -i '' 's|\$password = "estudioLucmar_4321";|\$password = "";|g' "$db_file"
        sed -i '' 's|\$database = "u212050690_estudiolucmar";|\$database = "u212050690_estudiolucmar";|g' "$db_file"
        echo "Development database connection details updated in $db_file"
    else
        echo "Database connection file $db_file does not exist."
    fi
}

# Check if the ./src directory exists
if [ -d "./src" ]; then
    echo "Starting processing in ./src"
    # Start processing from the ./src directory
    process_files "./src"
    echo "All matching strings have been processed."
else
    echo "Directory ./src does not exist."
fi

# Update the development database connection details
update_dev_db_connection
