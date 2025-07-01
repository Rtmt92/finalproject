FROM node:18

WORKDIR /app

# copie package.json et package-lock.json pour installer les deps
COPY package*.json ./

RUN npm install

# copie le reste du code
COPY . .

EXPOSE 3000

# lance le mode "watch" de react-scripts
CMD ["npm", "start"]
