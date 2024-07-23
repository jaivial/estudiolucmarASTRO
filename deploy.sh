#!/bin/bash

# Add all changes to the staging area
git add .

# Commit the changes with a message
git commit -m "deployment"

# Push the changes to the main branch on the origin remote
git push origin main

# Switch to the dist branch
git checkout dist

# Merge changes from the main branch into the dist branch
git merge main

# Stage only changes from the dist directory
git add dist

# Commit the changes with a message
git commit -m "Update dist directory after merging main"

# Push the changes to the dist branch on the origin remote
git push dist-branch dist

# Switch back to the main branch
git checkout main
