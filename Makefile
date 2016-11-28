# Makefile for building the project

app_name=githubmergetracker
project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
sign_dir=$(build_dir)/sign
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
package_name=$(app_name)
cert_dir=$(HOME)/.nextcloud/certificates

all: appstore

clean:
	rm -rf $(build_dir)

appstore: clean
	mkdir -p $(sign_dir)
	rsync -av \
	--exclude=.git \
	$(project_dir) $(sign_dir)
	cd $(sign_dir)
	@echo "Signing…"
	tar -cvzf $(build_dir)/$(app_name).tar.gz \
		-C $(sign_dir) $(app_name)
	openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(sign_dir)/../$(app_name).tar.gz | openssl base64