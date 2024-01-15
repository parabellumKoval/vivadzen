# Builder image
FROM node:16-alpine as builder

# Copy all files into the container
COPY ./../src/frontend/package.json /var/www/html/package.json

# Set up the working directory
WORKDIR /var/www/html

RUN apk update && apk upgrade
RUN apk add git
# RUN cp .env.dev .env

RUN npm install && npm cache clean --force

# COPY . .

ENV PATH ./node_modules/.bin/:$PATH