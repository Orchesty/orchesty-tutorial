FROM node:slim as precache

RUN apt-get update && apt-get update -y && apt-get install -y yarn

# Install node packages
WORKDIR /tmp/_node
COPY package.json ./
COPY yarn.lock ./
RUN yarn install --production

FROM node:slim as build

WORKDIR /srv/app
COPY --from=precache /tmp/_node /srv/app
COPY ./ /srv/app

RUN npm run build

FROM node:slim as prod

ENV APP_PORT=8000
ENV NODE_ENV=prod

WORKDIR /srv/app
COPY --from=build /srv/app/dist/src /srv/app
COPY --from=precache /tmp/_node /srv/app

RUN npm install pm2 -g

CMD pm2-runtime server.js
