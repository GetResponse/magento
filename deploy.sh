#!/bin/sh

TMP_PATH="$(pwd)/tmp"
GIT_PATH="$(pwd)"
GITHUB_PATH="$TMP_PATH/github"

FILES_TO_DELETE=(
  ".docker"
  ".gitattributes"
  ".gitignore"
  ".gitlab-ci.yml"
  ".junie"
  ".php-cs-fixer.dist.php"
  "compose.yml"
  "deploy.sh"
  "Makefile"
  "phpstan.neon"
  "phpstan-baseline.neon"
)

mkdir -p "$TMP_PATH"

# ASK INFO
echo "--------------------------------------------"
echo "      RELEASER      "
echo "--------------------------------------------"
read -p "TAG AND RELEASE VERSION: " VERSION
echo "--------------------------------------------"
echo ""
echo "Before continuing, confirm that you have done the following :)"
echo ""
read -p " - Added a changelog for $VERSION to CHANGELOG.md file?"
read -p " - Updated version in composer.json file?"
read -p " - Updated version in module.xml file?"
read -p " - Set stable tag in the README.txt file to $VERSION?"
read -p " - Committed all changes up to GitLab?"
echo ""

# check current branch name
if git branch --show-current > /dev/null 2>&1 !== "master" ; then
    echo "You are not on master branch"
    exit 1
fi

# check, if GitLab tag exists
if ! git ls-remote --exit-code --tags origin "$VERSION" > /dev/null 2>&1 ; then
    echo "Tag $VERSION in GitLab not found"
    exit 1
fi

# validate version in CHANGELOG.md
if ! grep "^## \\[$VERSION\\]" "$GIT_PATH/CHANGELOG.md"; then
    echo "Version in CHANGELOG.md does not exists. Exiting."
    exit 1
fi

# validate version in composer.json
if ! grep "\"version\": \"$VERSION\"" "$GIT_PATH/composer.json"; then
    echo "Version in composer.json is not correct. Exiting."
    exit 1
fi

# validate version in module.xml
if ! grep "setup_version=\"$VERSION\"" "$GIT_PATH/etc/module.xml"; then
    echo "Version in composer.json is not correct. Exiting."
    exit 1
fi

read -p "PRESS [ENTER] TO BEGIN RELEASING $VERSION"
clear

if ! [ -d "$GITHUB_PATH" ]
then
    echo "Clone Github repository... this may take a while"
    git clone git@github.com:GetResponse/magento.git "$GITHUB_PATH" >/dev/null 2>&1
fi

echo "Applying changes to Github repository"
git pull
git archive master | tar -x -C "$GITHUB_PATH"

echo ""
echo "Remove unused files"
for file in "${FILES_TO_DELETE[@]}"
do
  rm -rf "$GITHUB_PATH/$file"
done

echo ""
echo  "Add new files to Github repository"
cd $GITHUB_PATH && git add .

echo ""
echo "Github Status:"
cd $GITHUB_PATH && svn status

echo ""
read -p "Press [ENTER] to commit release $VERSION to Github"
echo ""

echo "Commiting to Github... this may take a while"
cd $GITHUB_PATH && git commit -m "Release $VERSION" || { echo "Unable to commit."; exit 1; }

echo "Creating new tag..."
cd $GITHUB_PATH && git tag -a $VERSION -m 'New plugin version released'

cd $GITHUB_PATH && git push

rm -rf "$TMP_PATH"
echo "Release done."