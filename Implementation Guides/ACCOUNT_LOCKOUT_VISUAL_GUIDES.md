# Account Lockout System - Visual Guides & Flowcharts

## 🎯 System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    ACCOUNT LOCKOUT SYSTEM                       │
└─────────────────────────────────────────────────────────────────┘

                          LOGIN ATTEMPT
                               │
                    ┌──────────┴──────────┐
                    │                     │
              ✅ SUCCESS            ❌ FAILED
                    │                     │
                    │            [Check Rate Limit]
                    │                     │
                    │          ┌──────────┴──────────┐
                    │          │                     │
                    │       < 5 attempts        >= 5 attempts
                    │          │                     │
                    │       LOGIN FAIL          🔒 LOCKED
                    │          │                     │
                    │    [Store in cache]    [Expire in 60 min]
                    │          │                     │
                    │    Retry again          ┌──────┴──────┐
                    │                         │             │
                    │                    WAIT 60min    UNLOCK
                    │                         │             │
                    │                    ✅ Auto         ┌──┴──┬──┬───┐
                    │                                     │     │  │   │
                    │                                   CLI   DASH EMAIL
                    │                                    |     |   |
                    │                                    └──┬──┴─┬─┴─┬──┘
                    │                                       │    │   │
                    │                                    CLEARED CLEARED
                    │                                       │    │   │
                    │◄──────────────────────────────────────┴────┴───┘
                    │
              ✅ LOGIN SUCCESS
                    │
              [Authenticate User]
                    │
              [Create Session]
                    │
              [Redirect to Dashboard]
```

---

## 🔓 Unlock Methods Flowchart

```
┌──────────────────────┐
│   USER IS LOCKED     │
└──────────────────────┘
           │
    ┌──────┼──────────────────┬────────────────┐
    │      │                  │                │
    ▼      ▼                  ▼                ▼
┌────┐ ┌──────┐         ┌──────────┐    ┌──────────┐
│ 1  │ │  2   │         │    3     │    │    4     │
│AUTO│ │ CLI  │         │ DASHBOARD│    │  EMAIL   │
└────┘ └──────┘         └──────────┘    └──────────┘
  │       │                  │               │
  │   [Admin]            [Admin]         [User]
  │       │                  │               │
  │   Terminal          Web Browser      Email Click
  │       │                  │               │
  │   Command            UI Button        Link
  │       │                  │               │
  │   Unlock             Unlock           Unlock
  │       │                  │               │
  └───┬───┴──────┬───────────┴───────┬──────┘
      │          │                   │
      ▼          ▼                   ▼
  [Clear Cache] [Clear Cache]  [Clear Cache]
      │          │                   │
      ▼          ▼                   ▼
    ✅ UNLOCKED
      │
      └──────────────────────┬─────────────────┘
                             │
                      [User Can Login]
                             │
                          ✅ SUCCESS
```

---

## 📊 Admin Dashboard Flow

```
┌────────────────────────────────────────┐
│   ADMIN GOES TO /admin/account-locks   │
└────────────────────────────────────────┘
                   │
            [Check Permission]
                   │
        ┌──────────┴──────────┐
        │                     │
      ✅ ADMIN              ❌ NOT ADMIN
        │                     │
        ▼                     ▼
   [Load Page]          [Redirect to Login]
        │
        ▼
┌───────────────────────┐
│  VIEW LOCKED ACCOUNTS │
│  ─────────────────── │
│ Email  │ IP │ Time   │
│ user1  │192.│ 45min  │
│ user2  │203.│ 30min  │
│ user3  │198.│ 55min  │
└───────────────────────┘
        │
        ├─────────────────────┬──────────────┐
        │                     │              │
        ▼                     ▼              ▼
    [Unlock]           [Unlock All]    [Edit Settings]
        │                     │              │
        └─────────┬───────────┴──┬───────────┘
                  │              │
                  ▼              ▼
            [Confirm]      [Save Settings]
                  │              │
                  ▼              ▼
          [Clear Cache]    [Update .env]
                  │              │
                  └─────┬────────┘
                        │
                        ▼
                    ✅ DONE
                        │
                  [Refresh Page]
                        │
              [Show Updated Data]
```

---

## 📧 Email Unlock Flow

```
┌─────────────────────────────────────────────┐
│  USER ACCOUNT LOCKED (Too Many Attempts)    │
└─────────────────────────────────────────────┘
                    │
              [Admin Decides]
                    │
        ┌───────────┴───────────┐
        │                       │
        ▼                       ▼
    [Send Email]           [Do Nothing]
        │                       │
        ▼                       │
    ┌─────────────────────┐    │
    │ EMAIL GENERATED     │    │
    │ ─────────────────── │    │
    │ To: user@email.com  │    │
    │ Subject: Your Acct  │    │
    │   is Locked         │    │
    │                     │    │
    │ [Click to unlock]   │    │
    │ Link: /auth/unlock? │    │
    │ email=user...&      │    │
    │ signature=xxx       │    │
    │ (expires 24h)       │    │
    └─────────────────────┘    │
        │                       │
        ▼                       │
    ┌──────────────────┐        │
    │  EMAIL SENT      │        │
    │ (via queue)      │        │
    └──────────────────┘        │
        │                       │
        ▼                       │
    [User Receives Email]       │
        │                       │
        ▼                       │
    [User Clicks Link]          │
        │                       │
        ▼                       │
    [Verify Signature]          │
        │                       │
    ┌───┴───┐                   │
    │       │                   │
  ✅ VALID ❌ INVALID          │
    │       │                   │
    ▼       ▼                   │
[Unlock] [404 Error]            │
    │       │                   │
    ▼       ▼                   │
[Clear  [Show               │
[Cache] Error Message]      │
    │                       │
    └───────────┬───────────┘
                │
                ▼
           [User Logged Out]
                │
                ▼
           [Login Page]
                │
                ▼
        [User Logs In]
                │
                ▼
            ✅ SUCCESS
```

---

## 🔧 CLI Command Flow

```
┌──────────────────────────────────────────────────┐
│  Admin Types: php artisan auth:unlock-account    │
│               john@example.com                   │
└──────────────────────────────────────────────────┘
                    │
        [Verify User Exists]
                    │
        ┌───────────┴──────────┐
        │                      │
      ✅ FOUND              ❌ NOT FOUND
        │                      │
        ▼                      ▼
    [Query Locks]          [Error: User not found]
        │                      │
        ▼                      └─► EXIT
    [Get Locked IPs]
        │
    ┌───┴────┬────────┐
    │        │        │
  0 IPs   1 IP    2+ IPs
    │        │        │
    ▼        ▼        ▼
 [No  [Auto]  [Interactive
  Lock]│      │    Selection]
    │        │        │
    ▼        ▼        ▼
[Error] [Unlock]  [Show List]
        │              │
        └────┬─────────┘
             │
             ▼
        [Clear Cache]
             │
             ▼
        [Log Activity]
             │
             ▼
        [Show Success]
             │
             ▼
        [Prompt Again?]
```

---

## ⚙️ Configuration Change Flow

```
┌────────────────────────────────────────┐
│   Admin Opens /admin/account-locks     │
└────────────────────────────────────────┘
                    │
                    ▼
        ┌─────────────────────┐
        │ SETTINGS SECTION    │
        │ ─────────────────── │
        │ Max Attempts:  [5]◄─┤─ User Changes to 3
        │ Duration: [60]◄─┤─ User Changes to 120
        │ □ Enable Rate Limit◄─ User Checks/Unchecks
        │ □ Email Unlock◄─ User Checks/Unchecks
        │                     │
        │ [SAVE SETTINGS]     │
        └─────────────────────┘
                    │
                    ▼ [User Clicks Save]
        ┌───────────────────────┐
        │ VALIDATE INPUT        │
        │ ─────────────────── │
        │ Max: 1-20? ✅       │
        │ Duration: 1-1440? ✅│
        │ Valid booleans? ✅   │
        └───────────────────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │ UPDATE .env FILE      │
        │ ─────────────────── │
        │ SECURITY_MAX_LOGIN_  │
        │ ATTEMPTS=3            │
        │ SECURITY_LOCKOUT_     │
        │ DURATION=120          │
        │ SECURITY_RATE_LIMIT_  │
        │ ENABLED=1             │
        └───────────────────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │ NOTIFY CONFIG         │
        │ ─────────────────── │
        │ Config cached but     │
        │ needs clear           │
        └───────────────────────┘
                    │
                    ▼
        ┌───────────────────────┐
        │ SHOW SUCCESS MESSAGE  │
        │ ─────────────────── │
        │ "Settings updated    │
        │  successfully"        │
        └───────────────────────┘
                    │
                    ▼
        [Refresh /admin/account-locks]
                    │
                    ▼
        [Show New Settings in Form]
```

---

## 🔒 Rate Limiting Logic

```
┌───────────────────────────┐
│  USER ATTEMPTS LOGIN      │
└───────────────────────────┘
            │
            ▼
    [Check Config]
            │
   [Rate Limiting Enabled?]
            │
    ┌───────┴────────┐
    │                │
   YES               NO
    │                │
    ▼                ▼
[Continue]    [Allow Login]
    │                │
    ▼                └──► EXIT
┌─────────────────────┐
│ BUILD CACHE KEY     │
│ ─────────────────── │
│ throttle:{email}    │
│         |{ip}       │
│                     │
│ Example:            │
│ throttle:john@ex... │
│ com|192.168.1.100   │
└─────────────────────┘
    │
    ▼
┌──────────────────────────┐
│ CHECK CACHE              │
│ ──────────────────────── │
│ Key exists?              │
└──────────────────────────┘
    │
    ├─────────┬─────────┐
    │         │         │
   NO        YES       EXPIRED
    │         │         │
    ▼         ▼         ▼
[Count=0] [Get Count]  [Delete]
    │         │         │
    ▼         ▼         ▼
    └────┬────┴────┬────┘
         │         │
         ▼         ▼
    [Check Max Attempts]
         │
    ┌────┴─────────────────┐
    │                      │
Count < Max         Count >= Max
    │                      │
    ▼                      ▼
[Increment]          🔒 LOCKED
[Store in Cache]     │
[Allow Login]        └──► ERROR MESSAGE
    │                    "Too many
    │                     attempts"
    ▼
[User Login]
```

---

## 📈 Activity Logging Flow

```
┌──────────────────┐
│ UNLOCK EVENT     │
└──────────────────┘
        │
    Method?
        │
    ┌───┼─────┬──────┬────────┐
    │   │     │      │        │
  AUTO CLI  DASHBOARD EMAIL   
    │   │     │      │        │
    └───┴─┬───┴──┬───┴────┬───┘
        │  │     │        │
        ▼  ▼     ▼        ▼
    [Log Event]
        │
    ┌───┴────────────────────────────┐
    │                                │
    ▼                                ▼
[storage/logs/]              [activity_logs table]
[laravel.log]                (if configured)
    │                                │
    ▼                                ▼
[2026-01-31 10:35:00]        [id: 123]
[laravel.INFO:]              [user_id: 1]
[Account unlocked]           [action: unlocked]
[john@example.com]           [email: john@ex...]
[(IP: 192.168.1.100)]       [ip_address: 192...]
[(Method: cli)]              [created_at: timestamp]
    │                                │
    └───────────────┬────────────────┘
                    │
                    ▼
            [Available for Review]
                    │
            ┌───────┴────────┐
            │                │
        [Logs Page]      [Reports]
            │                │
        [Search]         [Analytics]
            │                │
        [Filter]         [Export]
```

---

## 🎯 User Journey - Locked Out

```
┌─────────────────────┐
│ USER VISITS LOGIN   │
└─────────────────────┘
        │
        ▼
┌──────────────────┐
│ ENTERS EMAIL     │
│ ENTERS PASSWORD  │ (Wrong)
└──────────────────┘
        │
        ▼
    ❌ FAILED (1/5)
        │
        ▼
    Retry
        │
        ▼
    ❌ FAILED (2/5)
        │
        ▼
    Retry
        │
        ▼
    ❌ FAILED (3/5)
        │
        ▼
    Retry
        │
        ▼
    ❌ FAILED (4/5)
        │
        ▼
    Retry
        │
        ▼
    ❌ FAILED (5/5)
        │
        ▼
    🔒 ACCOUNT LOCKED
        │
        ├──────────────────────┬──────────────┬─────────────┐
        │                      │              │             │
        ▼                      ▼              ▼             ▼
    WAIT 60min           ASK ADMIN      CHECK EMAIL   TRY ELSEWHERE
        │                      │              │             │
        ▼                      ▼              ▼             ▼
   [Auto-unlock]      [Admin unlocks]  [Click link]   [Contact support]
        │                      │              │             │
        │                      │              ▼             │
        │                      │        [Account unlocked]  │
        │                      │              │             │
        └──────────────────┬───┴──────────┬───┴─────────┬───┘
                           │             │             │
                           ▼             ▼             ▼
                    ✅ CAN LOGIN
                           │
                    [User Logs In]
                           │
                           ▼
                    ✅ AUTHENTICATED
```

---

## 🛡️ Security Layers

```
┌────────────────────────────┐
│  EMAIL UNLOCK REQUEST      │
│  /auth/unlock-account?...  │
└────────────────────────────┘
            │
            ▼
    ┌──────────────────┐
    │ SECURITY LAYER 1 │
    │ SIGNATURE CHECK  │
    │ ─────────────── │
    │ Is signature     │
    │ valid?           │
    └──────────────────┘
            │
    ┌───────┴─────────┐
    │                 │
   ✅ VALID       ❌ INVALID
    │                 │
    ▼                 ▼
[Continue]      [404 ERROR]
    │                 │
    ▼                 └─► EXIT
    ┌──────────────────┐
    │ SECURITY LAYER 2 │
    │ EMAIL VALIDATION │
    │ ─────────────── │
    │ User exists?     │
    │ Email matches?   │
    └──────────────────┘
            │
    ┌───────┴─────────┐
    │                 │
   ✅ VALID       ❌ INVALID
    │                 │
    ▼                 ▼
[Continue]      [ERROR]
    │                 │
    ▼                 └─► EXIT
    ┌──────────────────┐
    │ SECURITY LAYER 3 │
    │ EXPIRATION CHECK │
    │ ─────────────── │
    │ Link not        │
    │ expired?        │
    │ (24 hours)      │
    └──────────────────┘
            │
    ┌───────┴─────────┐
    │                 │
   ✅ VALID       ❌ EXPIRED
    │                 │
    ▼                 ▼
[Continue]      [404 ERROR]
    │                 │
    ▼                 └─► EXIT
    ┌──────────────────┐
    │ SECURITY LAYER 4 │
    │ IP VERIFICATION │
    │ ─────────────── │
    │ IP in request    │
    │ valid?           │
    └──────────────────┘
            │
    ┌───────┴─────────┐
    │                 │
   ✅ VALID       ❌ INVALID
    │                 │
    ▼                 ▼
[Continue]      [ERROR]
    │                 │
    ▼                 └─► EXIT
    ┌──────────────────┐
    │ UNLOCK ACCOUNT   │
    │ ─────────────── │
    │ Clear cache      │
    │ Log event        │
    │ Send notification│
    └──────────────────┘
            │
            ▼
    ✅ ACCOUNT UNLOCKED
            │
    [Redirect to /login]
```

---

## 📊 Cache Table Overview

```
┌────────────────────────────────────────────────────────────┐
│ CACHE TABLE - Rate Limiting Entries                        │
├────────────────────────────────────────────────────────────┤
│ key                              │ value │ expiration      │
├────────────────────────────────────────────────────────────┤
│ throttle:john@ex.com|192.168.1.1 │  5    │ 1674045600      │
│ throttle:jane@ex.com|203.0.113.45│  3    │ 1674045200      │
│ throttle:bob@ex.com|198.51.100.20│  2    │ 1674046100      │
└────────────────────────────────────────────────────────────┘

Format of key:
  throttle:{email}|{ip_address}

Value = number of failed attempts

Expiration = unix timestamp when entry expires (auto-deleted)

Example Query:
  SELECT * FROM cache 
  WHERE key LIKE 'throttle:%' 
  AND expiration > NOW()
```

---

## 🔄 Complete System Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│              COMPLETE SYSTEM LIFECYCLE                      │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐
│  1. DEPLOY   │ System installed, configured
└──────────────┘
       │
       ▼
┌──────────────────┐
│ 2. CONFIGURE     │ Set max attempts, duration
└──────────────────┘
       │
       ▼
┌──────────────────┐
│ 3. USERS LOGIN   │ Normal authentication
└──────────────────┘
       │
       ▼
┌──────────────────┐
│ 4. FAILED LOGIN  │ Wrong password entered
└──────────────────┘
       │
   [Count < 5]
       │
       ▼
┌──────────────────┐
│ 5. RETRY         │ User tries again
└──────────────────┘
       │
   [Count = 5]
       │
       ▼
┌──────────────────┐
│ 6. LOCKED        │ Account locked
└──────────────────┘
       │
       ├──────────────────────┬──────────────┬──────────────┐
       │                      │              │              │
       ▼                      ▼              ▼              ▼
   WAIT 60min          ADMIN UNLOCKS   EMAIL UNLOCK   USER CONTACTS
       │                      │              │              │
       └──────────────────┬───┴──────┬───────┴──────────┬───┘
                          │         │                  │
                          ▼         ▼                  ▼
┌──────────────────────────────────────────────────────────┐
│ 7. ACCOUNT UNLOCKED - Cache entry cleared               │
└──────────────────────────────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────┐
│ 8. USER CAN LOGIN - Normal authentication resumed        │
└──────────────────────────────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────┐
│ 9. SESSION CREATED - User authenticated                 │
└──────────────────────────────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────┐
│ 10. ACTIVITY LOGGED - Unlock event recorded             │
└──────────────────────────────────────────────────────────┘
```

---

## 🎓 Learning Path Diagram

```
START
  │
  ├─────────────────────────────────────────┐
  │                                         │
  ▼ (5 min)                         (30 min total)
Quick Reference                      Complete Guide
  │                                    │
  ├─ Commands                         ├─ Features
  ├─ Scenarios                        ├─ Configuration
  ├─ Troubleshooting                  ├─ Security
  └─ Quick Answers                    ├─ Development
                                      └─ Customization
  │                                         │
  ▼ (30 min)                        (90 min total)
Complete Guide                     Testing Guide
  │                                    │
  ├─ Architecture                     ├─ Phase 1: Install
  ├─ Features Deep Dive               ├─ Phase 2: Lockout
  ├─ Configuration Options            ├─ Phase 3: Auto-unlock
  ├─ Security Details                 ├─ Phase 4: CLI
  └─ Advanced Topics                  ├─ Phase 5: Dashboard
                                      ├─ Phase 6: Email
  │                                    ├─ Phase 7: Config
  ▼ (45 min)                         ├─ Phase 8: Toggles
API Documentation                    ├─ Phase 9: Logging
  │                                  ├─ Phase 10: Security
  ├─ Endpoints                       └─ Results Summary
  ├─ Code Examples                   │
  ├─ Integration                     ▼ (Varies)
  ├─ Database Schema            FAQ & Troubleshooting
  └─ Testing Code                   │
                                    ├─ Common Issues
  │                                ├─ Solutions
  ▼                               ├─ Advanced Tips
Mastery!                           └─ Getting Help
  │
  └─► IMPLEMENTATION READY
```

---

## ✨ Summary

This visual guide shows:
- ✅ System architecture and data flow
- ✅ All 4 unlock methods
- ✅ Admin dashboard workflow
- ✅ Email unlock security
- ✅ CLI command execution
- ✅ Configuration changes
- ✅ Rate limiting logic
- ✅ Activity logging
- ✅ User journey
- ✅ Security layers
- ✅ Complete lifecycle
- ✅ Learning path

**Use these diagrams for quick understanding and reference!** 📊

