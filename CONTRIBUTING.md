# 🛠️ Contribution Guidelines
## Project: ToDo & Co

---

> ### 1. Purpose of This Documentation
This guide is intended for developers contributing to the project.  
It outlines the best practices to ensure clean, maintainable, and consistent code that aligns with Symfony standards.

---

> ### 2. Technical Requirements

- **PHP**: version 8.2 or higher
- **Symfony**: version 7.2
- **Database**: MySQL or SQLite
- **Local server**: Symfony CLI or XAMPP

---

> ### 3. Contribution Rules

> #### 3.1 Branch Naming

- `feature/feature-name` → for new features
- `fix/bug-name` → for bug fixes
- `hotfix/emergency` → for urgent production patches

> #### 3.2 Code Conventions

- **PHP CS Fixer** is used to apply coding conventions. Before submitting a PR, ensure that your code has been automatically fixed by running `php-cs-fixer fix` in the project.
- Follow **PSR-12** to adhere to Symfony's coding standards (PHP CS Fixer is configured to apply these rules).
- **Indentation**: 4 spaces (no tabs).
- **Class and Method Names**: Use **CamelCase** for classes and methods.
- **Variable Names**: Use **snake_case** for variable names and method parameters.
- **Line Length**: Limit lines to **120 characters**.
- **Spaces**: Leave a space after commas and before parentheses in control structures.

> #### 3.3 Commits

- Use clear commit messages in English:
    - ✅ `fix: prevent empty title submission`
    - ✅ `feat: add sorting system`
- Avoid vague messages like `update`, `test`, or `wip`.

---

> ### 4. Testing Before Any Pull Request

- Make sure everything works locally (`symfony server:start`)
- No visible bugs or errors
- Manually test your changes
- Add unit or functional tests if applicable
    - Unit tests should cover individual methods or functions.
    - Functional tests should cover larger workflows, such as submitting forms or accessing protected routes.

> **Test Coverage**: Aim for a minimum of **70% test coverage** across the codebase.
- Use PHPUnit for writing and running tests.
- Generate code coverage reports using `phpunit --coverage-html var/coverage` to ensure adequate test coverage.

---

> ### 5. Merge Process

1. Create a **pull request** (PR) targeting `main` .
2. Provide a clear description of your changes, including any relevant context or background.
3. Ask for a review from another developer if necessary.
4. Only merge after receiving approval from a team member.

---

> ### 6. Project Structure Overview

- **Controllers**: `/src/Controller`
- **Entities**: `/src/Entity`
- **Templates**: `/templates`
- **Forms**: `/src/Form`
- **Security Configuration**: `/config/packages/security.yaml`
- **Tests**: `/tests`

---

> ### 7. Specific Best Practices

- **Never store plain text passwords**: Always encode passwords using Symfony’s password hasher.
- **Roles**: Use clear roles like `ROLE_USER`, `ROLE_ADMIN`. The default role for a user is `ROLE_USER`.
- **Access Control**: Protect routes using `access_control` or Symfony’s `#[IsGranted()]` annotations.
- **Use Voters**: Implement Symfony **Voters** to control access to sensitive actions, such as user management or task deletion.
- **Document new features**: If your change modifies the system's behavior, ensure that the new functionality is well documented.

---

> ### 8. Communication

- Use internal communication channels (e.g., Slack, project board) for questions or issues.
- Mention any important changes directly in your PR description.
- Provide additional context if the change impacts other developers.

---

> ### 9. Additional Notes

- **Fixtures**: When creating new functionality that requires sample data, use Symfony’s **Fixtures** to populate the database in a consistent and testable manner.
- **Performance Audits**: Perform regular performance audits using tools like Xdebug or Blackfire to monitor and optimize the application's performance.
- **Code Quality Audit**: Utilize tools like PHPStan to analyze code quality and address issues related to type safety and other best practices.

> **Plan for Reducing Technical Debt**: Regularly review the project for potential improvements and refactor areas identified by the code quality or performance audits. Provide a plan for addressing technical debt, and document it clearly for the team.

---

> Please follow this documentation to maintain high code quality across the project.

> Thank you for your contributions! 😊