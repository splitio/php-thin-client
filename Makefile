PHP ?= php
COMPOSER ?= composer
PHPUNIT ?= vendor/bin/phpunit
COVERAGE_FILE ?= coverage.xml
COVERAGE_HTML_PATH ?= coverage.html

SOURCES := $(shell find src -iname "*.php")

.phony: help cleandeps deps test test-args display-coverage

default: help

## Remove all downloaded dependencies & lock file
cleandeps:
	rm -Rf vendor/ composer.lock

## Install/Update dependencies
deps: composer.lock vendor/

## Update window sizes for send/receive buffers used in sockets API for using SEQPACKET with large payloads (Linux only)
sock-buf-size-enhance:
	sudo echo '8000000' > /proc/sys/net/core/wmem_max
	sudo echo '8000000' > /proc/sys/net/core/rmem_max

## Run all tests (args optional)
test:
	XDEBUG_MODE=coverage $(PHPUNIT) -c phpunit.xml  --coverage-clover $(COVERAGE_FILE) $(ARGS)

## Display a human readable coverage report after (re)generating it if required.
display-coverage: $(COVERAGE_HTML_PATH)
	open $(COVERAGE_HTML_PATH)/index.html

$(COVERAGE_HTML_PATH): $(SOURCES)
	XDEBUG_MODE=coverage $(PHPUNIT) -c phpunit.xml  --coverage-html $(COVERAGE_HTML_PATH)/

composer.lock vendor/: composer.json
	$(COMPOSER) update

$(PHPUNIT): deps

# Help target borrowed from: https://docs.cloudposse.com/reference/best-practices/make-best-practices/
## This help screen
help:
	@printf "Available targets:\n\n"
	@awk '/^[a-zA-Z\-\_0-9%:\\]+/ \
		{ \
			helpMessage = match(lastLine, /^## (.*)/); \
			if (helpMessage) { \
				helpCommand = $$1; \
				helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
				gsub("\\\\", "", helpCommand); \
				gsub(":+$$", "", helpCommand); \
				printf "  \x1b[32;01m%-35s\x1b[0m %s\n", helpCommand, helpMessage; \
			} \
	    } \
	    { lastLine = $$0 }' $(MAKEFILE_LIST) 2> /dev/null | sort -u
	@printf "\n"
