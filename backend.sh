#!/bin/bash

# Define the search and replace strings
SEARCH_STRING='http://localhost:8000/'
REPLACE_STRING='http://localhost:8000/backend/'

# Define the directory to search within
TARGET_DIR="./src"

# Check if the target directory exists
if [ -d "$TARGET_DIR" ]; then
    # Find and replace strings, excluding .DS_Store files
    find "$TARGET_DIR" -type f ! -name '.DS_Store' -exec sed -i "" "s|$SEARCH_STRING|$REPLACE_STRING|g" {} +
    echo "Replacement complete."
else
    echo "Directory $TARGET_DIR does not exist."
    exit 1
fi
