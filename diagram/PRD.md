# PRD — Subsistem Notifikasi Perawatan Mesin & Monitoring SPK

## 1. Overview

Sistem ini bertujuan untuk:

* Monitoring counter mesin dari DB master (MINA_IOT_PKL)
* Menghasilkan status mesin: NORMAL, WARNING, DANGER
* Membuat notifikasi otomatis
* Mendukung lifecycle SPK: NULL → PROGRESS → DONE
* Menyediakan dashboard supervisor & teknisi

Sistem tidak menghitung counter sendiri. Data counter terdapat di DB Master yang nantinya akan dibuat logic treshold untuk trigger warning/danger status

---

# 2. Goals

* Monitoring mesin otomatis via scheduler
* Warning ketika counter >= threshold
* Menyimpan history status mesin
* Escalation ke danger jika tidak ditindak
* Teknisi dapat membuat & update SPK
* Supervisor dapat melihat status mesin (history list beserta status dan daten terakhir kali si scheduler aktif, baik itu yang masih normal/warning/danger)

---

# 3. Non Goals

* Tidak menyimpan persentase
* Tidak menghitung counter dari raw data
* Tidak membuat analytics kompleks
* Tidak membuat forecasting

---

# 4. User Roles

## Supervisor

Melihat dashboard monitoring yang berisi modul-modul:
* Melihat history dan status mesin berdasarkan filter terakhir kali scheduler dijalankan (berdasarkan PLANT nya masing-masing)
* Melihat SPK progress

## Teknisi

* Menerima notifikasi warning/danger data PLC
* Memulai SPK (diluar sistem)
* Update SPK progress dan selesai

---

# 5. Status Lifecycle Mesin

NORMAL (< 75% threshold)
↓
WARNING (>=75% threshold)
↓
DANGER (>=100% limit & bereskalasi)
↓
PROGRESS (teknisi mulai SPK)
↓
DONE (SPK selesai)

---

# 6. Scheduler Flow

Scheduler berjalan tiap interval (tiap hari jam 07:00)

Flow:

1. Query DB Master
2. Ambil counter & limit mesin
3. Hitung threshold (logic)
4. Ambil state mesin dari DB sistem
5. Tentukan status baru
6. Jika status berubah → insert notification
7. Kirim email teknisi

---

# 7. Database Schema

## Table: machines

Tujuan: menyimpan data master mesin

id (PK)
machine_code
machine_name
plant
line
limit_count
threshold_percent
is_active
created_at
updated_at

---

## Table: machine_alert_states

Tujuan: menyimpan state terakhir mesin

machine_code
current_status (NORMAL/WARNING/DANGER/PROGRESS/DONE)
last_counter
warning_at
danger_at
spk_status (NONE/PROGRESS/DONE)
updated_at

---

## Table: notifications

Tujuan: history semua perubahan status mesin

id (PK)
machine_code
status
counter
message
created_at
is_read

---

# 8. Modules

## Modul 1 — Dashboard Monitoring (Supervisor)

Tujuan: melihat kondisi mesin realtime

Tampilan:

## Machine   Counter   Limit   Status     SPK

MINA-01   15        20      WARNING    -
MINA-02   20        20      DANGER     PROGRESS
MINA-03   3         20      NORMAL     -

Fitur:

* Filter plant
* Filter line
* Filter status
* Search mesin
* Sorting counter
* Refresh realtime
* Klik detail mesin
* View history

---

## Modul 2 — Notifikasi Teknisi

Tujuan: teknisi menerima warning dan update SPK

Tampilan:

## Machine   Status   Counter   Time

MINA-01   WARNING  15        10:05
MINA-02   DANGER   20        10:10

Fitur:

* View notifikasi
* Start SPK
* Update progress
* Mark done
* Filter status
* Sorting terbaru

---

## Modul 3 — Master Mesin

Tujuan: mengelola data mesin

Tampilan:

## Machine   Plant   Line   Limit   Status

MINA-01   A       1      20      Active
MINA-02   A       1      20      Active

Fitur:

* Tambah mesin
* Edit mesin
* Set limit
* Set threshold
* Assign plant
* Assign line
* Active / inactive

---

# 9. Notification Rules

WARNING
counter >= limit * threshold_percent

DANGER
counter >= limit
AND status sebelumnya WARNING
AND SPK belum dibuat

PROGRESS
teknisi klik start SPK

DONE
teknisi klik selesai

---

# 10. Email Notification

Trigger:

* WARNING
* DANGER

Subject:
WARNING MESIN MINA-01

Body:
Machine : MINA-01
Status  : WARNING
Counter : 15
Limit   : 20

Segera lakukan pengecekan

---

# 11. Dashboard Summary Cards

Total Mesin
Warning
Danger
Progress
Done

Example:
Total : 50
Warning : 5
Danger : 2
Progress : 3
Done : 10

---

# 12. System Flow

Scheduler
↓
Query DB Master
↓
Hitung threshold
↓
Cek state mesin
↓
Update state
↓
Insert notification
↓
Send email

---

# 13. UI Navigation

Sidebar:
Dashboard Monitoring
Notifications
Machine Master

Supervisor:

* Dashboard Monitoring
* Machine Master

Teknisi:

* Notifications
