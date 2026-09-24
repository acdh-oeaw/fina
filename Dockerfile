FROM docker.io/library/mediawiki:1.43.9

# Add FINA extensions, skins, and dependencies here.

RUN printf "memory_limit=512M\nmax_execution_time=360\nmax_input_time=360\nupload_max_filesize=256M\npost_max_size=257M\n" \
    > /usr/local/etc/php/conf.d/zz-fina.ini \
 && printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=20000\n" \
    > /usr/local/etc/php/conf.d/zz-fina-opcache.ini

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
