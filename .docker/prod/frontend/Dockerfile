# Builder image
FROM node:16-alpine as builder

# Copy all files into the container
COPY ./../src/frontend /var/www/html

# Set up the working directory
WORKDIR /var/www/html

RUN apk update && apk upgrade
RUN apk add git

RUN npm install && npm cache clean --force

COPY . .

ENV PATH ./node_modules/.bin/:$PATH