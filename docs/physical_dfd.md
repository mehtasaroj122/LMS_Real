# Physical DFD (Deployment-level view)

This physical DFD maps the LMS logical processes to physical components: clients, load balancer, web server, application servers, background workers, databases, caches, search index, file storage, notification gateways, payment gateway, and SSO.

```mermaid
flowchart LR
  subgraph Clients[Clients]
    WebBrowser["Web Browser / SPA"]
    MobileApp["Mobile App"]
  end

  LB["Load Balancer / CDN"]
  Web["Web Server (NGINX)"]
  App["App Server (PHP / Laravel)"]
  Workers["Background Workers / Queue (Redis + Workers)"]
  Search["Search Index (Elasticsearch)"]
  Cache["Cache (Redis)"]
  DB["Primary DB (MySQL/Postgres)"]
  FileStore["File Storage (S3 / Local)"]
  SMTP["Email / SMS Gateway"]
  PGW["Payment Gateway"]
  SSO["Auth Provider (SSO/LDAP)"]

  WebBrowser -->|HTTPS| LB
  MobileApp -->|HTTPS / API| LB
  LB --> Web
  Web -->|fastcgi / proxy| App

  App -->|read/write| DB
  App -->|cache read/write| Cache
  App -->|index / search| Search
  App -->|upload/download| FileStore
  App -->|enqueue job| Workers
  App -->|authenticate| SSO
  App -->|send email/sms| SMTP
  App -->|process payment (redirect/API)| PGW

  Workers -->|process jobs: issue/return, notifications, reconcile| App
  Workers -->|read/write| DB
  Workers -->|update cache| Cache
  Workers -->|send notifications| SMTP
  Workers -->|call payment API for refunds| PGW

  Search -->|index updates| App
  DB -->|replicate / export| Search
  FileStore -->|serve files (CDN)| LB

```

File: [docs/physical_dfd.mmd](docs/physical_dfd.mmd)
