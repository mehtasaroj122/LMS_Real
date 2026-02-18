# Logical DFD — Complete

This document contains the full logical Data Flow Diagram for the Library Management System. It shows logical processes, data stores, external actors, authentication and notification flows, and inter-process data movement.

```mermaid
flowchart TB
  %% Logical DFD for Library Management System (all logical processes and data flows)

  %% External actors
  Students["Students"]
  Staff["Library Staff"]
  Admins["Administrators"]
  PaymentGateway["Payment Gateway"]
  EmailSMS["Email / SMS Provider"]

  %% Logical Processes (top-level and decomposed)
  P1["1.0 Catalog & Search"]
  P1a["1.0.1 Accept Query"]
  P1b["1.0.2 Search / Filter"]
  P1c["1.0.3 Return Results"]

  P2["1.1 Catalog Management"]
  P2a["1.1.1 Add / Import Books"]
  P2b["1.1.2 Update Metadata"]
  P2c["1.1.3 Inventory Adjustments"]

  P3["1.2 Loan / Issue Management"]
  P3a["1.2.1 Validate Request"]
  P3b["1.2.2 Reserve / Check Availability"]
  P3c["1.2.3 Issue Book"]
  P3d["1.2.4 Return & Inspect"]
  P3e["1.2.5 Overdue & Fine Calculation"]

  P4["1.3 Payments & Fines"]
  P4a["1.3.1 Create Fine"]
  P4b["1.3.2 Present Payment Options"]
  P4c["1.3.3 Process Payment"]

  P5["1.4 Users & Settings"]
  P5a["1.4.1 Register User"]
  P5b["1.4.2 Update Profile / Roles"]
  P5c["1.4.3 Manage Privileges"]

  Notif["Notification Service"]
  Auth["Authentication Service"]

  %% Data stores
  DB_Books[("Book Catalog")]
  DB_Users[("User Profiles")]
  DB_Loans[("Loan Records")]
  DB_Fines[("Fines / Payments")]
  SearchIndex[("Search Index")]

  %% External interactions
  Students -->|"search requests"| P1a
  P1a --> P1b
  P1b -->|"lookup catalog"| SearchIndex
  P1b -->|"lookup catalog"| DB_Books
  P1b --> P1c
  P1c --> Students

  Staff -->|"submit book data"| P2a
  P2a --> P2b
  P2b --> DB_Books
  P2c --> DB_Books

  %% Loan flows
  Students -->|"borrow request"| P3a
  P3a -->|"verify eligibility"| DB_Users
  P3a -->|"check holds"| DB_Loans
  P3a --> P3b
  P3b -->|"check copies"| DB_Books
  P3b --> P3c
  P3c -->|"create loan record"| DB_Loans
  P3c -->|"decrement availability"| DB_Books
  P3c --> Notif

  P3d -->|"update return"| DB_Loans
  P3d -->|"inspect and flag"| P3e
  P3d -->|"increment availability"| DB_Books

  P3e -->|"calculate fines"| DB_Fines
  P3e --> P4a

  %% Payments
  Students -->|"request to pay fine"| P4b
  P4b --> P4c
  P4c -->|"charge"| PaymentGateway
  PaymentGateway -->|"payment result"| P4c
  P4c -->|"record payment"| DB_Fines
  P4c --> Notif

  %% Users
  Admins -->|"create/update user"| P5a
  P5a -->|"store profile"| DB_Users
  P5b --> DB_Users
  P5c --> DB_Users

  %% Auth and notifications
  P5a -->|"provision credentials"| Auth
  P3a -->|"authenticate user"| Auth
  P4c -->|"send receipt"| Notif
  Notif -->|"deliver email/sms"| EmailSMS

  %% Search index sync
  P2b -->|"update index"| SearchIndex
  DB_Books -->|"replicate changes"| SearchIndex

  %% Reporting / audits
  DB_Loans -->|"loan history"| P5b
  DB_Fines -->|"payment history"| P5b

```

File: [docs/logical_dfd.mmd](docs/logical_dfd.mmd)
