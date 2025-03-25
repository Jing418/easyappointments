# 使用 Node.js 官方镜像作为基础镜像
FROM node:16

# 设置工作目录
WORKDIR /app

# 复制 package.json 和 package-lock.json
COPY package*.json ./

# 安装依赖
RUN npm install

# 复制应用代码
COPY . .

# 构建 React 应用
RUN npm run build

# 启动开发服务器
CMD ["npm", "start"]
