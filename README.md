# ✅ ToDo & Co – Task Management Symfony Application

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/45f6611b55394e50a2d81573ce2270de)](https://app.codacy.com/gh/Psylvie/TodoList/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

**ToDo & Co** is a Symfony application designed to help users manage their daily tasks. It features an authentication system with role management (user/admin) and a set of authorization rules. The application aims to provide an intuitive dashboard for easy personal task management.

---

## 📱 Features

The application offers the following features:

- **Sign up and log in** with an authentication system. Users can register and log in securely.

- **Add, edit, and delete tasks**. Users can create new tasks, modify existing ones, and delete tasks they own.

- **View a list of your tasks** with sorting and filtering options. Tasks can be organized by due date, priority, or status.

- **Mark tasks as complete**. Users can mark their tasks as completed to easily track progress.

- **User role management** (administrator and user roles).

    - Users can be assigned roles during account creation. Roles include **ROLE_USER** (regular user) and **ROLE_ADMIN** (administrator).

    - Administrators have extended privileges, including access to the user management pages and the ability to change user roles.

- **Task ownership**. Each task is automatically associated with the authenticated user when created. For tasks created before the update, they are assigned to a special "anonymous" user.

- **Task permissions**:

    - Only the creator of a task can delete it.

    - Tasks associated with the "anonymous" user can only be deleted by an administrator.

- **Administrator-only access** to user management pages. Only users with **ROLE_ADMIN** can view and manage other users.

---

## ⚙️ Prerequisites

Avant de commencer, assurez-vous d’avoir installé :

Before you begin, ensure you have the following installed:

- **PHP** >= 8.2
- **Symfony** >= 7.2
---

## 🚀 Project Installation

## 1. Clone the repository ##

   ```bash
   git clone https://github.com/Psylvie/TodoList.git
   ```

## 2. Install dependencies ##

   ```bash
   composer install
   ```

## 3.  Create your database ##
-> Open the .env file and configure your database connection details. Look for the DATABASE_URL variable and update it with your username, password, and database name. For example:

 ``` bash
    DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
 ```

->  In your terminal, run the following commands:
  ``` bash
     php bin/console doctrine:database:create
  ```

 ``` bash
   php bin/console doctrine:migrations:migrate
 ```

-> Load the fixtures
 ``` bash
      php bin/console doctrine:fixtures:load 
 ```

-> Start the Symfony server

   ``` bash
     symfony server:start
   ```
This will typically make the application accessible at http://127.0.0.1:8000

-> To log in as a user, choose a username from the generated list (password = password123)
   ``` bash
      php bin/console doctrine:query:sql "SELECT username FROM app_user"
   ```

-> To log in as admin, choose a username from the generated list (password = password123)
   ``` bash
      php bin/console doctrine:query:sql "SELECT username FROM user WHERE roles LIKE '%ROLE_ADMIN%'"
   ```
---

## 4. Create and populate your test database ##

-> Create the test database
```bash
  php bin/console doctrine:database:create --env=test
```
-> Run the migrations on the test database

```bash
  php bin/console doctrine:migrations:migrate --env=test
```

-> Load the fixtures on the test database

```bash
  php bin/console doctrine:fixtures:load --env=test
```

-> Run the test suite with coverage
 ``` bash
    vendor\bin\phpunit --coverage-html=coverage
```
➡️ The coverage report will be available in the coverage/index.html file.

# 🤝 Contribution

See the `CONTRIBUTING.md` file for development conventions, contribution steps, and project best practices.

---

# 🧪 Audit & Quality

A code audit and a performance audit can be performed with:
- PHPStan
- Symfony Profiler
- Codacy

---

#  📄 License # 

Educational project – OpenClassrooms – All rights reserved.


# 🛠️ Support & contact
For any questions or suggestions regarding this project, feel free to contact me via email at the following address: peuzin.sylvie.sp@gmail.com

I am open to any ideas for improvements or additional features.

# 🙇 Author #
<p text align= center> Sylvie PEUZIN  
<br> Application Developer PHP/SYMFONY  


LinkedIn: [@sylvie Peuzin](https://www.linkedin.com/in/sylvie-peuzin/) </p>
