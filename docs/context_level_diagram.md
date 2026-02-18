# Context Level (Level 0) Diagram

This Level-0 context diagram shows the `Library Management System` boundary and external actors/systems that interact with it.

```mermaid
%% Context (Level 0) diagram for Library Management System
flowchart LR
    subgraph External_Actors[External Actors]
      WebClients["Web / Mobile Clients"]
      Students["Students"]
      Staff["Library Staff"]
      Admins["Administrators"]
    end

    ExternalEmail["Email / SMS Provider"]
    PaymentGateway["Payment Gateway"]
    AuthProvider["Auth Provider (LDAP / SSO)"]
    Database["Database / Data Store"]

    WebClients -->|"UI / API (HTTP)"| LMS["Library Management System"]
    Students -->|"Search / Request / Borrow / Return / Pay Fines"| LMS
    Staff -->|"Issue / Return / Manage Catalog / Process Requests"| LMS
    Admins -->|"Manage Users, Settings, Reports"| LMS

    LMS -->|"Persist / Query"| Database
    LMS -->|"Send notifications (email/sms)"| ExternalEmail
    LMS -->|"Process payments / refunds"| PaymentGateway
    LMS -->|"Authenticate users"| AuthProvider
```

File: [docs/context_level_diagram.mmd](docs/context_level_diagram.mmd)
