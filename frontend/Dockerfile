# ---------- Tahap 1: build dengan Node.js ----------
FROM node:22-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Alamat API ditanam saat build (bukan rahasia). Bisa diganti lewat --build-arg.
ARG VITE_API_URL=http://localhost:8001/api
ENV VITE_API_URL=$VITE_API_URL

RUN npm run build

# ---------- Tahap 2: sajikan dengan Nginx ----------
FROM nginx:1.27-alpine

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80