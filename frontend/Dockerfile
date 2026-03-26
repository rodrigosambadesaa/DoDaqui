FROM node:20-alpine

# Directorio de trabajo de la app frontend.
WORKDIR /app

# Copiamos primero manifests para aprovechar cache de capas de npm install.
COPY package*.json ./
RUN npm install

# Copiamos el resto del código una vez resueltas dependencias.
COPY . .

# Puerto del servidor Express interno.
EXPOSE 3000

# Comando de arranque (server.js vía npm start).
CMD ["npm", "start"]
