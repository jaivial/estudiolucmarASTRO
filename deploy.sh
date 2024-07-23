# !/bin/bash

# Add all changes to the staging area
git add .

# Commit the changes with a message
git commit -m "deployment main"

# Push the changes to the main branch on the origin remote
git push origin main

# Switch to the dist branch
git checkout dist

# Merge changes from the main branch into the dist branch
git merge main

# Delete everything except the dist folder
find . -mindepth 1 -maxdepth 1 ! -name 'dist' -exec rm -rf {} +

# Move contents from dist folder to the root level
mv dist/* .

# Remove the now-empty dist folder
rmdir dist

# Add all changes to the staging area
git add .

# Commit the changes with a message
git commit -m "deployment dist"

# Push the changes to the dist-branch branch on the origin remote
git push origin dist-branch

