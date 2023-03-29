
# Makefile for building the project

# building the javascript
build: deps
	npm run build

build-dev: deps
	npm run build-dev

watch: deps
	npm run watch

# general
deps:
	npm install --deps

# localization
l10n-clone:
	python ./duplicate-lang.py --path=../l10n bg_BG:bg cs_CZ:cs fi_FI:fi hu_HU:hu nb_NO:nb sk_SK:sk th_TH:th

l10n-extract: deps
	npm run l10n-extract

l10n-compile: deps
	npm run l10n-compile
	cd ../l10n; ./l10n.pl music write

# releasing
release:
	./release.sh
