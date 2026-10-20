# AGENTS.md

AI 编码代理操作指南 - Laravel + Vue 3 项目

## 📋 项目概览
- 后端: Laravel 10.x, PHP ^8.1
- 前端: Vue 3, Vite, Tailwind CSS, Element Plus
- 状态管理: Pinia
- 路由: Vue Router 4

## ⚙️ 开发命令

### 安装
```bash
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### 后端开发
```bash
# 启动 Laravel 服务器
php artisan serve

# 格式化 (Laravel Pint)
vendor/bin/pint
vendor/bin/pint --test  # 仅检查

# 测试
php artisan test                                      # 所有测试
php artisan test --filter=TestClassName               # 单个测试类
php artisan test --filter=TestClassName::testMethod   # 单个测试方法
php artisan test --coverage                           # 带覆盖率

# 数据库
php artisan migrate
php artisan migrate:fresh
php artisan db:seed
```

### 前端开发
```bash
# 开发服务器 (热重载)
npm run dev

# 生产构建
npm run build
```

## 📝 代码风格

### PHP 规范
- `declare(strict_types=1);` 文件顶部
- PSR-4 自动加载
- 4 空格缩进，120 字符行长度
- `[]` 短数组语法

### 命名约定
- 类: StudlyCase (UserController)
- 方法: camelCase (getUserById)
- 变量: camelCase ($userId)
- 常量: UPPER_SNAKE_CASE (MAX_COUNT)
- Vue 组件: PascalCase (UserProfile.vue)

### 类型声明
- 返回类型: 必须声明 `function foo(): string {`
- 参数类型: 必须声明 `function foo(string $a): void {`
- 可空: `?string`, 联合: `string|int`, 数组: `array<string,int>`

### 导入顺序
```php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;          // 框架类
use App\Models\User;                  // App 类 (字母顺序)
use App\Services\UserService;
```

### Laravel 架构
- 控制器: 保持薄，逻辑委托给服务
- 模型: 定义关联，用 $fillable/$guarded，用作用域
- 服务: App\Services，构造函数注入，单一职责
- 表单请求: `php artisan make:request`
- API 资源: 转换模型响应

### 错误处理
- 使用异常 (ValidationException, ModelNotFoundException)
- 请求必须验证 (Form Request 或控制器验证)
- Try/Catch: 先特异后一般，绝不空块
- 数据库操作使用事务

## 🎨 前端规范

### Vue 3
- 使用 Composition API (`<script setup>`)
- Props: 使用 `defineProps` + 类型推断或 `defineProps<{name: string}>()`
- Emits: 使用 `defineEmits<{update: [value: string]}>()`
- Refs: 基础类型用 `ref`，对象/数组用 `reactive`
- Composables: 复用逻辑放在 `src/composables/`

### 组件结构
```
<template>
  <!-- 模板内容 -->
</template>

<script setup lang="ts">
// 1. 导入
import { ref, computed } from 'vue'
import type { SomeType } from '@/types'

// 2. Props/Emits
defineProps<{ id: number }>()
const emit = defineEmits<{ submit: [data: FormData] }>()

// 3. 响应式数据
const count = ref(0)
const loading = computed(() => count.value > 0)

// 4. 方法
const handleSubmit = () => {}
</script>

<style scoped>
/* 组件样式 */
</style>
```

### Tailwind CSS + Element Plus
- 优先使用 Tailwind 工具类
- Element Plus 组件用于复杂 UI
- 自定义样式用 scoped `<style>`

### Pinia Stores
- 按功能拆分 stores: `stores/user.ts`, `stores/project.ts`
- State 使用 `ref`，Getters 使用 `computed`
- Actions 保持纯逻辑

## 🔒 安全与测试

### 安全
- 认证: Laravel Sanctum
- 授权: Gates 和 Policies
- 输入验证: 永不信任用户输入
- 输出转义: 使用 Vue 插值语法 `{{ }}`

### 测试
- Feature: 端到端 API 测试
- Unit: 服务和模型测试
- 命名: 描述性，snake_case `test_user_creation`
- 数据: 使用 Factories，不用种子

## 🔧 开发工作流

1. **修改代码**: 先理解现有模式，匹配风格，最小改动
2. **添加功能**: 规划 → 咨询 → 实现 → 测试 → 审查
3. **提交前**: 运行 `vendor/bin/pint` 和受影响的测试
4. **验证**: lint 通过、测试通过、功能正常

## 📚 参考
- Laravel: https://laravel.com/docs/10.x
- Vue 3: https://vuejs.org/
- Tailwind: https://tailwindcss.com/
- Element Plus: https://element-plus.org/

---
*此文件指导 AI 代理遵循项目模式。请随项目演进及时更新。*
