FROM node:19-alpine as precache

RUN npm i -g pnpm

# Install node packages
WORKDIR /tmp/_node
COPY package.json ./
COPY pnpm-lock.yaml ./
RUN pnpm install

# Install prod node packages
WORKDIR /tmp/_node_prod
COPY package.json ./
COPY pnpm-lock.yaml ./
RUN pnpm install --production

FROM node:19-alpine as build

WORKDIR /srv/app
COPY --from=precache /tmp/_node /srv/app
COPY ./ /srv/app

RUN npm run build

FROM node:19-alpine as prod

ENV APP_PORT=8000
ENV NODE_ENV=prod

WORKDIR /srv/app
COPY --from=build /srv/app/dist/src /srv/app
COPY --from=precache /tmp/_node_prod /srv/app

CMD node index.js
