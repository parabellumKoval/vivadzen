# Builder image
FROM node:16-alpine as builder

# Copy all files into the container
COPY ./../src/frontend /var/www/html

# Set up the working directory
WORKDIR /var/www/html

RUN apk update \
    && apk upgrade \
    && apk add git \
    && cp .env.prod .env


RUN npm install && npm cache clean --force
RUN npm run build

# Specify the host variable
# ENV HOST 0.0.0.0

# Expose the Nuxt port
EXPOSE 3000

# Init the command
# CMD ["npm", "run", "build"]
# CMD ["yarn", "start"]

