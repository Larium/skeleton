install:
	cp -r .env.dist .env

cleanup:
	rm -rf .git
	rm Makefile
