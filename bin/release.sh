#!/usr/bin/env bash
echo "${RELEASE_NOTES}";
replace-in-file "== Changelog ==" "== Changelog ==\n\n${RELEASE_NOTES}" ../readme.txt