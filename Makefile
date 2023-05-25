PHP ?= php
COMPOSER ?= composer
PHPUNIT ?= vendor/bin/phpunit

default: help

.phony: test help

deps:
	$(COMPOSER) update

$(PHPUNIT): deps

## Update window sizes for send/receive buffers used in sockets API (required for UTs)
seqpacket-pre-test: $(PHPUNIT)
	sudo echo '2097152' > /proc/sys/net/core/wmem_max
	sudo echo '2097152' > /proc/sys/net/core/rmem_max

## Run all tests (args optional)
test:
	$(PHPUNIT) -c phpunit.xml --testsuite all $(ARGS)

## Run phpunit with specified args
test-args:
	$(PHPUNIT) $(ARGS)

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
