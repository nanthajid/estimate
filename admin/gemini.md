 ที่ ./admin
	1.ระบบหลังบ้าน (Admin)
		สามารถ:
			- เพิ่ม/แก้ไขข้อมูลพนักงาน
			- อัปโหลดรูปพนักงาน
			- Generate QR Code อัตโนมัติ
			- Export Excel / PDF
			- ค้นหาข้อมูลย้อนหลังได้
	2.เทคโนโลยีที่ต้องการ
		Frontend:
			- มี card แสดงข้อมูล สถิติ
			- Responsive Modern UI
			- รองรับ Touch Screen
	3.Backend:
		- ใช้ php
		- เก็บข้อมูลลง mysql database
		- รองรับ php pdo
	4. Design Style:
			- Modern Minimal
			- สีโทนองค์กร น้ำเงิน / ขาว / เขียว
			- Card UI มุมโค้ง
			- ใช้ Font ภาษาไทยอ่านง่าย
			- ใช้งานง่ายแม้ผู้สูงอายุ

6. ระบบ Login ด้วย LINE (LINE Login Integration)

    

รายละเอียด (Description)
ระบบนี้ใช้สำหรับจัดการการยืนยันตัวตนของผู้ใช้งาน (Authentication) ผ่าน **LINE Login API** โดยใช้มาตรฐาน OAuth 2.0 และ OpenID Connect เพื่อดึงข้อมูลโปรไฟล์พื้นฐาน (ชื่อ, รูปโปรไฟล์, และ User ID) มาใช้ในระบบของเรา

## ⚙️ สิ่งที่ต้องเตรียม (Prerequisites)

ก่อนเริ่มรันโปรเจกต์ ต้องเตรียมข้อมูลจาก [LINE Developers Console](https://developers.line.biz/) ดังนี้:

1. สร้าง Provider และ Channel ประเภท **LINE Login**
2. ตั้งค่าสถานะ Channel เป็น **Published**
3. ไปที่แท็บ LINE Login แล้วเพิ่ม **Callback URL** (เช่น `http://localhost:3000/api/auth/line/callback`)

## 🔑 การตั้งค่าตัวแปรสภาพแวดล้อม (Environment Variables)

สร้างไฟล์ `.env` ที่ Root directory ของโปรเจกต์ และกำหนดค่าดังนี้:

```env
LINE_CHANNEL_ID=your_channel_id_here
LINE_CHANNEL_SECRET=your_channel_secret_here
LINE_CALLBACK_URL=http://localhost:3000/api/auth/line/callback
JWT_SECRET=your_jwt_secret_for_internal_session
```



- sequenceDiagram
  participant User
  participant Frontend
  participant Backend
  participant LINE API

    User->>Frontend: คลิกปุ่ม "Login with LINE"
    Frontend->>LINE API: Redirect ไปหน้า LINE Authorization URL
    LINE API-->>User: แสดงหน้าต่างยินยอมให้เข้าถึงข้อมูล
    User->>LINE API: กดยอมรับ (Authorize)
    LINE API->>Backend: Redirect กลับมาที่ Callback URL พร้อม`code` และ `state`
    Backend->>LINE API: นำ `code` ไปแลก Access Token
    LINE API-->>Backend: ส่งกลับ Access Token & ID Token
    Backend->>LINE API: ใช้ Access Token ดึงข้อมูล Profile (ถ้าจำเป็น)
    LINE API-->>Backend: ส่งข้อมูล User Profile (userId, displayName, pictureUrl)
    Backend->>Backend: ตรวจสอบ/บันทึกข้อมูลลง Database & สร้าง Session (JWT)
    Backend-->>Frontend: Redirect กลับหน้าแรกพร้อม Login Session
