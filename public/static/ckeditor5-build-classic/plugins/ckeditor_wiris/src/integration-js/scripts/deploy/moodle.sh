# This deploy file synchronizes the JS API branches with it's Moodle dependencies.
#
# The structure among the API and it's dependencies is the following:
#                 JS API                                 Dependency
# master          x---x---x               master         o---o---o
# feature branch  x---x---x---X           feature branch o---o---o---O
# 
# Steps to synchronize:
# 1.- Create a new branch in the dependency repository with the same name.
# 2.- Change the referency for the API inside package.json dependency file from the npm repository for the current branch.
# 3.- Compile the dependency.
# 4.- Push the compiled file, the package.json and the version file - if exists - to the dependency repository.

# Dependencies:
# - Atto
#   * GitHub repository: moodle-atto_wiris.git/
#   * Source file: none.
#   * Compiled file: core.js

# - TinyMCE for Moodle:
#   * GitHub repository: moodle-tinymce_tiny_mce_wiris.git/
#   * Source file: src/editor.src.js
#   * Compiled file: editor.js


# Clean repository
if [[ $BRANCH == "stable" ]]
then
    echo "Avoid deploy in stable branch";
    exit 0;
fi
git clean -dfx;
# Compile API
npm install;
npm run build;
# Set GitHub environment
git config --global user.email "travis@travis-ci.org";
git config --global user.name "Travis CI";
# Atto
git clone --branch master https://github.com/wiris/moodle-atto_wiris.git;
cd moodle-atto_wiris;
git checkout -B $BRANCH
if [[ `git branch --list $BRANCH` ]]
then
    echo "Branch name $BRANCH already exists... pull content."
    git pull origin $BRANCH
fi
mv -f ../core.js .
if [[ `git status --porcelain` ]]
then
    git add $(git diff --name-only);
    git commit -m "mathtype-integration-js-dev $(date +%Y%m%d%H) development version";
    git push https://$GH_TOKEN@github.com/wiris/moodle-atto_wiris.git $BRANCH > /dev/null 2>&1;
else
    echo "No changes. Skiping deploy in moodle-atto_wiris repository".
fi
# Cleaning up
cd ..
rm -rf github.com/wiris/moodle-atto_wiris.git;

# TinyMCE
git clone --branch master https://github.com/wiris/moodle-tinymce_tiny_mce_wiris;
cd moodle-tinymce_tiny_mce_wiris/tinymce/src
git checkout -B $BRANCH
if [[ `git branch --list $BRANCH` ]]
then
    echo "Branch name $BRANCH already exists... pull content."
    git pull origin $BRANCH
fi
# Change mathtype-integration-js-dev dependency to the new branch.
sed -i "s/\@wiris\/mathtype-integration-js-dev\":[[:space:]]\"\^[0-9]*.[0-9]*.[0-9]*\"/\@wiris\/mathtype-integration-js-dev\": \"wiris\/mathtype-integration-js-dev\#$BRANCH\"/" package.json
# Install dependencies.
npm install
# Compile editor_plugin.src.js source file.
npm run build
rm -rf node_modules
cd ../..
# Commiting the package.json file with the dependency updated.
git add tinymce/src/package.json
git commit -m "$BRANCH: @wiris/integration-js dependency updated to $BRANCH"
git push https://$GH_TOKEN@github.com/wiris/moodle-tinymce_tiny_mce_wiris.git $BRANCH > /dev/null 2>&1
rm tinymce/src/package-lock.json
git status --porcelain
if [[ `git status --porcelain` ]]
then
    git add $(git diff --name-only);
    git commit -m "mathtype-integration-js-dev $(date +%Y%m%d%H) development version";
    echo "push"
    git push https://$GH_TOKEN@github.com/wiris/moodle-tinymce_tiny_mce_wiris.git $BRANCH #> /dev/null 2>&1;
else
    echo "No changes. Skiping deploy in moodle-tinymce_tiny_mce_wiris repository".
fi
