#!/usr/bin/env bash

commit=$1
if [ -z ${commit} ]; then
    commit=$(git tag --sort=-creatordate | head -1)
    if [ -z ${commit} ]; then
        commit="master";
    fi
fi

# Remove old release
rm -rf FfuenfCategoryUrlSlash FfuenfCategoryUrlSlash-*.zip

# Build new release
mkdir -p FfuenfCategoryUrlSlash
git archive ${commit} | tar -x -C FfuenfCategoryUrlSlash
composer install --no-dev -n -o -d FfuenfCategoryUrlSlash
( find ./FfuenfCategoryUrlSlash -type d -name ".git" && find ./FfuenfCategoryUrlSlash -name ".gitignore" && find ./FfuenfCategoryUrlSlash -name ".gitmodules" && find ./FfuenfCategoryUrlSlash -name ".php_cs.dist" && find ./FfuenfCategoryUrlSlash -name "phpspec.yml" && find ./FfuenfCategoryUrlSlash -name ".travis.yml" && find ./FfuenfCategoryUrlSlash -name "build.sh" && find ./FfuenfCategoryUrlSlash -name "phpunit.xml.dist" && find ./FfuenfCategoryUrlSlash -name "tests") | xargs rm -r
zip -r FfuenfCategoryUrlSlash-${commit}.zip FfuenfCategoryUrlSlash