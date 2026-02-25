# 📚 图书馆管理系统 (Quản Lý Thư Viện)

一个功能完善的图书馆管理系统，基于 Laravel 8 框架开发，支持图书借阅、库存管理、订单处理、在线支付等核心功能。

---

## 📋 目录

- [项目简介](#项目简介)
- [主要功能](#主要功能)
- [技术栈](#技术栈)
- [安装指南](#安装指南)
- [配置说明](#配置说明)
- [使用说明](#使用说明)
- [常见问题](#常见问题)
- [项目结构](#项目结构)

---

## 🎯 项目简介

这是一个现代化的图书馆管理系统，旨在帮助图书馆管理员高效管理图书、读者、借阅记录等业务。系统支持在线借阅、支付、库存管理、报表统计等功能。

**项目特点：**
- ✅ 完整的图书管理功能
- ✅ 读者信息管理
- ✅ 在线借阅系统
- ✅ 支付集成（VNPay）
- ✅ 库存管理
- ✅ 权限管理系统
- ✅ 数据报表和统计
- ✅ 响应式设计

---

## 🚀 主要功能

### 1. 图书管理
- 📖 图书信息管理（标题、作者、出版社、分类等）
- 🖼️ 图书图片上传和管理
- 📊 图书库存管理
- 🔍 高级搜索功能
- 📈 图书统计和报表

### 2. 读者管理
- 👤 读者信息管理
- 🎫 读者证管理
- 📝 读者借阅历史
- ⚠️ 读者状态管理（激活/暂停）

### 3. 借阅管理
- 📚 图书借阅处理
- 🔄 图书归还处理
- ⏰ 借阅期限管理
- 📦 借阅订单状态跟踪（11种状态）
- 🚚 物流配送管理

### 4. 订单和支付
- 💰 在线支付集成（VNPay）
- 🛒 借阅购物车功能
- 📋 订单管理
- 💳 钱包系统
- 🎫 优惠券管理

### 5. 库存管理
- 📦 库存入库管理
- 📤 库存出库管理
- 🔄 库存转移
- 🔧 库存维修管理
- 📊 库存报表

### 6. 权限管理
- 👥 用户角色管理（管理员、员工、读者）
- 🔐 基于权限的访问控制
- 📝 操作日志记录
- 🔍 审计日志

### 7. 报表统计
- 📊 借阅统计
- 📈 图书统计
- 👥 读者统计
- 💰 财务统计
- 📉 趋势分析

---

## 🛠️ 技术栈

### 后端
- **框架**: Laravel 8.75
- **PHP版本**: PHP 7.3+ 或 PHP 8.0+
- **数据库**: MySQL
- **认证**: Laravel Sanctum
- **权限管理**: Spatie Laravel Permission

### 前端
- **模板引擎**: Blade
- **CSS框架**: Bootstrap（推测）
- **JavaScript**: jQuery, Axios
- **构建工具**: Laravel Mix

### 第三方服务
- **支付**: VNPay 支付网关
- **云存储**: Cloudinary（图片存储）
- **OAuth**: Google 登录
- **Excel处理**: Maatwebsite Excel

### 开发工具
- **包管理**: Composer, NPM
- **版本控制**: Git

---

## 📦 安装指南

### 环境要求
- PHP >= 7.3 或 PHP >= 8.0
- Composer
- Node.js 和 NPM
- MySQL 5.7+ 或 MariaDB 10.3+
- Web服务器（Apache/Nginx）

### 安装步骤

#### 1. 克隆项目
```bash
git clone <repository-url>
cd quanlythuviennn
```

#### 2. 安装依赖
```bash
# 安装 PHP 依赖
composer install

# 安装前端依赖
npm install
```

#### 3. 配置环境
```bash
# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
php artisan key:generate
```

#### 4. 配置数据库
编辑 `.env` 文件，设置数据库连接信息：
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quanlythuvien
DB_USERNAME=root
DB_PASSWORD=
```

#### 5. 运行数据库迁移
```bash
# 运行迁移
php artisan migrate

# 运行数据填充（可选）
php artisan db:seed
```

#### 6. 创建存储链接
```bash
php artisan storage:link
```

#### 7. 编译前端资源
```bash
# 开发环境
npm run dev

# 生产环境
npm run prod
```

#### 8. 启动开发服务器
```bash
php artisan serve
```

访问 `http://localhost:8000` 查看应用。

---

## ⚙️ 配置说明

### 环境变量配置

#### 数据库配置
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### VNPay 支付配置
```env
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
```

**注意**: 
- 测试环境使用沙箱 URL
- 生产环境需要从 VNPay 获取真实的 TMN_CODE 和 HASH_SECRET

#### 邮件配置
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Cloudinary 配置（图片存储）
```env
CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name
```

#### Google OAuth 配置
```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## 📖 使用说明

### 管理员功能

#### 登录系统
1. 访问 `/login`
2. 使用管理员账号登录
3. 登录后自动跳转到管理后台

#### 图书管理
- **添加图书**: 通过库存管理 → 创建入库单 → 添加图书
- **编辑图书**: 访问 `/admin/books/{id}/edit`
- **隐藏/显示图书**: 在图书列表中使用隐藏/显示功能

#### 借阅管理
- **处理借阅**: 访问 `/admin/borrows`
- **确认订单**: 点击"确认订单"按钮
- **跟踪物流**: 使用物流日志功能跟踪配送状态
- **处理归还**: 点击"归还"按钮处理图书归还

#### 库存管理
- **创建入库单**: 访问 `/admin/inventory-receipts/create`
- **查看库存**: 访问 `/admin/inventory`
- **库存转移**: 在库存详情页使用转移功能

#### 报表查看
- **借阅报表**: 访问 `/admin/reports/borrows`
- **图书报表**: 访问 `/admin/reports/books`
- **读者报表**: 访问 `/admin/reports/readers`
- **高级统计**: 访问 `/admin/advanced-reports`

### 读者功能

#### 注册和登录
1. 访问 `/register` 注册新账号
2. 或使用 Google 账号登录
3. 登录后访问个人中心

#### 浏览图书
- 访问 `/books` 查看所有图书
- 使用搜索功能查找图书
- 点击图书查看详细信息

#### 借阅图书
1. 在图书详情页点击"加入借阅车"
2. 访问 `/borrow-cart` 查看借阅车
3. 选择要借阅的图书
4. 点击"结算"进入支付页面
5. 使用 VNPay 完成支付

#### 管理借阅
- **查看借阅记录**: 访问 `/account/borrowed-books`
- **确认收货**: 收到图书后点击"确认收货"
- **申请归还**: 点击"申请归还"按钮
- **上传归还照片**: 归还时上传照片作为凭证

#### 钱包管理
- **查看余额**: 访问 `/account/wallet`
- **查看交易记录**: 访问 `/account/wallet/transactions`
- **充值**: 通过支付系统充值到钱包

---

## ❓ 常见问题

### 1. VNPay 支付错误："Xác thực chữ ký thất bại"（签名验证失败）

**解决方案**:
1. 检查 `.env` 文件中的 `VNPAY_TMN_CODE` 和 `VNPAY_HASH_SECRET` 是否正确
2. 运行修复脚本：双击 `fix_vnpay_now.bat`
3. 清除缓存：`php artisan config:clear`
4. 访问 `/vnpay-debug` 检查配置

**详细说明**: 查看 `QUICK_START.txt` 或 `HUONG_DAN_SUA_LOI_VNPAY.md`

### 2. 图片无法显示

**解决方案**:
1. 确保已创建存储链接：`php artisan storage:link`
2. 检查 `storage/app/public` 目录权限
3. 检查图片路径配置

**详细说明**: 查看 `FIX_IMAGE_NOT_SHOWING.md`

### 3. 数据库迁移失败

**解决方案**:
1. 检查数据库连接配置
2. 确保数据库用户有足够权限
3. 手动运行 SQL 文件（如 `quanlythuvien.sql`）

### 4. 权限错误

**解决方案**:
1. 运行权限设置脚本：`PHÂN_QUYỀN_STAFF.bat`
2. 或查看 `PHÂN_QUYỀN_STAFF.md` 手动设置

### 5. 缓存问题

**解决方案**:
```bash
# 清除配置缓存
php artisan config:clear

# 清除应用缓存
php artisan cache:clear

# 清除路由缓存
php artisan route:clear

# 清除视图缓存
php artisan view:clear

# 或使用批处理文件
clear_cache.bat
```

---

## 📁 项目结构

```
quanlythuviennn/
├── app/                    # 应用核心代码
│   ├── Console/           # 命令行工具
│   ├── Http/              # HTTP 层（控制器、中间件等）
│   │   ├── Controllers/  # 控制器
│   │   └── Middleware/    # 中间件
│   ├── Models/            # 数据模型
│   ├── Services/          # 业务逻辑服务
│   └── ...
├── config/                # 配置文件
├── database/              # 数据库相关
│   ├── migrations/        # 数据库迁移文件
│   ├── seeders/          # 数据填充文件
│   └── factories/         # 模型工厂
├── public/                # 公共资源（入口文件）
├── resources/             # 资源文件
│   ├── views/            # Blade 模板
│   ├── css/              # CSS 文件
│   └── js/               # JavaScript 文件
├── routes/                # 路由定义
│   ├── web.php           # Web 路由
│   └── api.php           # API 路由
├── storage/               # 存储文件（日志、上传文件等）
├── tests/                 # 测试文件
├── vendor/                # Composer 依赖
├── .env                   # 环境配置文件
├── composer.json          # PHP 依赖配置
├── package.json           # Node.js 依赖配置
└── README.md             # 项目说明文档（本文件）
```

---

## 🔧 开发工具和脚本

项目包含多个辅助脚本，位于根目录：

### 数据库相关
- `create_wallet_tables.bat` - 创建钱包表
- `fix-database.bat` - 修复数据库

### 修复脚本
- `fix_vnpay_now.bat` - 修复 VNPay 配置
- `fix_storage_link.bat` - 创建存储链接
- `clear_cache.bat` - 清除缓存

### 权限设置
- `PHÂN_QUYỀN_STAFF.bat` - 设置员工权限

### 服务器管理
- `restart_server.bat` - 重启服务器

---

## 📝 更新日志

### 最新更新
- ✅ 完善借阅状态管理（11种状态）
- ✅ 集成 VNPay 支付系统
- ✅ 添加钱包功能
- ✅ 完善物流配送管理
- ✅ 添加权限管理系统
- ✅ 优化库存管理功能

---

## 🤝 贡献指南

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

---

## 📄 许可证

本项目采用 MIT 许可证。详情请查看 `LICENSE` 文件。

---

## 📞 支持

如有问题或建议，请：
1. 查看项目文档
2. 检查常见问题部分
3. 提交 Issue

---

## 🎉 致谢

感谢所有为这个项目做出贡献的开发者和用户！

---

**最后更新**: 2025年1月

**维护状态**: 积极维护中 ✅
