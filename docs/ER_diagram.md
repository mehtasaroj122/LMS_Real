# ER Diagram (Mermaid)

The following diagram was generated from the project's migrations. It includes primary keys, important columns, foreign keys, and cardinalities as enforced by the migrations.

```mermaid
%% Paste or include the contents of docs/ER_diagram.mmd here if your renderer supports external includes
erDiagram
    USERS {
        int id PK "primary key"
        enum role
        string name
        string email "unique"
        string phone
    }

    STUDENTS {
        int id PK
        int user_id FK
        int department_id FK
        string roll_no "unique"
    }

    STAFF {
        int id PK
        int user_id FK
        int department_id FK
    }

    DEPARTMENTS {
        int id PK
        string name "unique"
    }

    CATEGORIES {
        int id PK
        string name "unique"
    }

    BOOKS {
        int id PK
        int category_id FK
        string title
        string isbn "unique"
    }

    ISSUED_BOOKS {
        int id PK
        int book_id FK
        int student_id FK
        int issued_by FK
        date issue_date
        date due_date
        date return_date
    }

    FINES {
        int id PK
        int issued_book_id FK
        int student_id FK
        decimal amount
    }

    BOOK_REQUESTS {
        int id PK
        int student_id FK
        int book_id FK
        datetime request_date
    }

    STUDENT_PRIVILEGES {
        int id PK
        int student_id FK "unique"
    }

    %% Relationships
    USERS ||--o{ STUDENTS : "1 user to 0..* student profiles"
    USERS ||--o{ STAFF : "1 user to 0..* staff profiles"
    DEPARTMENTS ||--o{ STUDENTS : "1 department to many students"
    DEPARTMENTS ||--o{ STAFF : "1 department to many staff"
    CATEGORIES ||--o{ BOOKS : "1 category to many books"
    BOOKS ||--o{ ISSUED_BOOKS : "1 book to many issues"
    STUDENTS ||--o{ ISSUED_BOOKS : "1 student to many issues"
    ISSUED_BOOKS ||--o{ FINES : "1 issue to 0..* fines"
    STUDENTS ||--o{ FINES : "1 student to many fines"
    STUDENTS ||--o{ BOOK_REQUESTS : "1 student to many requests"
    BOOKS ||--o{ BOOK_REQUESTS : "1 book to many requests"
    STUDENTS ||--|| STUDENT_PRIVILEGES : "1 student to 0..1 privilege"
```

File: [docs/ER_diagram.mmd](docs/ER_diagram.mmd)
