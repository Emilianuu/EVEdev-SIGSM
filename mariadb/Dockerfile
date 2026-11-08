FROM mariadb:latest
COPY ./scripts_sql/*.sql /docker-entrypoint-initdb.d/
RUN chmod 0444 /docker-entrypoint-initdb.d/*.sql

