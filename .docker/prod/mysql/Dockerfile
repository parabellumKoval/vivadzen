# Image
FROM mysql

# Add mysql configurations
COPY .docker/prod/mysql/configs/my.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
RUN chmod 644 /etc/mysql/mysql.conf.d/mysqld.cnf
RUN chown mysql:mysql /etc/mysql/mysql.conf.d/mysqld.cnf

# Logs directory
RUN mkdir -p /var/log/mysql && chown mysql:mysql /var/log/mysql

CMD ["mysqld", "--defaults-file=/etc/mysql/mysql.conf.d/mysqld.cnf"]
# CMD ["mysqld"]