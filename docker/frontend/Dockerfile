FROM node:18-alpine

# Копируем остальной код
COPY ./frontend /home/app

# Устанавливаем рабочую директорию
WORKDIR /home/app

# Устанавливаем зависимости с учетом кэширования
RUN npm install --legacy-peer-deps

# Открываем порт для Vite
EXPOSE 5173

# Запускаем приложение
CMD ["npm", "run", "dev"]