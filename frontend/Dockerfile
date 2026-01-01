# Frontend Dockerfile for Next.js Application

# Use Node.js 25 as base image (matching the required version)
FROM node:25.2.1-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy all application files
COPY . .

# Expose port 3000
EXPOSE 3000

# Set environment to development by default
ENV NODE_ENV=development

# Start the development server
CMD ["npm", "run", "dev"]
