<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.html");
    exit;
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Staff Feedback System</title>
  
  <!-- Libraries (CDN) -->
  <script src="https://unpkg.com/react@18.3.1/umd/react.development.js"></script>
  <script>window.react = window.React;</script>
  <script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js"></script>
  <script>window.reactDom = window.ReactDOM;</script>
  <script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js"></script>
  <script src="https://unpkg.com/lucide-react@0.416.0/dist/umd/lucide-react.min.js"></script>
  
  <!-- Export Libraries -->
  <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
  
  <!-- QR Library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root {
      /* Corporate Palette: Blue / White / Green */
      --primary:   oklch(55% 0.16 250); /* Blue */
      --secondary: oklch(75% 0.18 145); /* Green */
      --bg:        oklch(98% 0.005 250);
      --surface:   oklch(100% 0 0);
      --fg:        oklch(20% 0.015 250);
      --muted:     oklch(55% 0.015 250);
      --border:    oklch(92% 0.01 250);
      --error:     oklch(60% 0.2 25);
      
      --radius-xl: 24px;
      --radius-lg: 16px;
      --radius-md: 12px;
      --radius-sm: 8px;
      
      --font-main: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Inter', 'Prompt', sans-serif;
      --shadow-sm: 0 2px 8px oklch(0% 0 0 / 4%);
      --shadow-md: 0 10px 30px -10px oklch(0% 0 0 / 8%);
    }

    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

    body {
      margin: 0;
      padding: 0;
      background: var(--bg);
      color: var(--fg);
      font-family: var(--font-main);
      display: flex;
      min-height: 100vh;
    }

    #root { display: flex; width: 100%; }

    /* Layout Structure */
    .sidebar {
      width: 260px;
      background: var(--surface);
      border-right: 1px solid var(--border);
      padding: 32px 16px;
      display: flex;
      flex-direction: column;
      gap: 32px;
      position: sticky;
      top: 0;
      height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: 32px;
      overflow-y: auto;
      max-width: 1200px;
      margin: 0 auto;
      width: 100%;
    }

    /* Typography & Buttons */
    h1, h2, h3 { margin: 0; letter-spacing: -0.02em; }
    .nav-item {
      padding: 12px 16px;
      border-radius: var(--radius-md);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--muted);
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .nav-item.active {
      background: var(--primary);
      color: white;
    }
    .nav-item:not(.active):hover {
      background: var(--bg);
      color: var(--fg);
    }

    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 24px;
      box-shadow: var(--shadow-md);
    }

    .btn {
      padding: 10px 20px;
      border-radius: var(--radius-md);
      border: none;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-secondary { background: var(--secondary); color: white; }
    .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
    .btn:active { transform: scale(0.97); }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main-content { padding: 16px; }
    }
  </style>
</head>
<body>
  <div id="root"></div>

  <script type="text/babel">
    const { useState, useEffect } = React;
    const { 
      LayoutDashboard, 
      Users, 
      MessageSquare, 
      Settings, 
      Plus, 
      Download, 
      QrCode, 
      Search,
      Edit2,
      Trash2,
      Camera
    } = LucideReact;
    const Modal = ({ title, isOpen, onClose, children }) => {
      if (!isOpen) return null;
      return (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: 20 }}>
          <div className="card" style={{ width: '100%', maxWidth: 500, position: 'relative', maxHeight: '90vh', overflowY: 'auto' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
              <h2 style={{ fontSize: 20 }}>{title}</h2>
              <button onClick={onClose} style={{ background: 'none', border: 'none', fontSize: 24, cursor: 'pointer', color: 'var(--muted)' }}>&times;</button>
            </div>
            {children}
          </div>
        </div>
      );
    };

    const QRCodeSVG = ({ value, size }) => {
      const [qrDataUrl, setQrDataUrl] = React.useState('');
      React.useEffect(() => {
        if (window.QRCode) {
          QRCode.toDataURL(value, { width: size, margin: 2 }, (err, url) => {
            if (err) console.error(err);
            else setQrDataUrl(url);
          });
        }
      }, [value, size]);
      return qrDataUrl ? <img src={qrDataUrl} style={{ maxWidth: '100%', width: size, height: size }} /> : <div style={{ width: size, height: size, background: '#eee' }}></div>;
    };

    function QrModal({ staff, isOpen, onClose }) {
      if (!isOpen || !staff) return null;
      // Construct the evaluation URL (assuming index.html is the root)
      const evaluationUrl = `${window.location.origin}${window.location.pathname.replace('/admin/', '/user_form/')}?id=${staff.staff_id}`;
      
      return (
        <Modal title="QR Code สำหรับพนักงาน" isOpen={isOpen} onClose={onClose}>
          <div style={{ textAlign: 'center', padding: '16px 0' }}>
            <div style={{ background: 'white', padding: 24, borderRadius: 16, display: 'inline-block', border: '1px solid var(--border)' }}>
               <QRCodeSVG value={evaluationUrl} size={200} />
            </div>
            <div style={{ marginTop: 24 }}>
              <h3 style={{ fontSize: 18 }}>{staff.name}</h3>
              <p style={{ color: 'var(--muted)', fontSize: 14 }}>{staff.position} • {staff.department}</p>
            </div>
            <div style={{ marginTop: 24, padding: 12, background: 'var(--bg)', borderRadius: 12, fontSize: 12, color: 'var(--muted)', wordBreak: 'break-all' }}>
              {evaluationUrl}
            </div>
            <button className="btn btn-primary" style={{ width: '100%', marginTop: 24, justifyContent: 'center' }} onClick={() => window.print()}>
              <Download size={18} /> พิมพ์ QR Code
            </button>
          </div>
        </Modal>
      );
    }

    function App() {
      const [view, setView] = useState('dashboard');

      return (
        <div style={{ display: 'flex', width: '100%' }}>
          <aside className="sidebar">
            <div style={{ padding: '0 16px', marginBottom: 16 }}>
              <h2 style={{ color: 'var(--primary)', display: 'flex', alignItems: 'center', gap: 10 }}>
                <img src="logo.png" style={{ width: 32, height: 32, objectFit: 'contain' }} alt="Logo" />
                ADMIN
              </h2>
            </div>
            <nav style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
              <div className={`nav-item ${view === 'dashboard' ? 'active' : ''}`} onClick={() => setView('dashboard')}>
                <LayoutDashboard size={20} /> แดชบอร์ด
              </div>
              <div className={`nav-item ${view === 'staff' ? 'active' : ''}`} onClick={() => setView('staff')}>
                <Users size={20} /> พนักงาน
              </div>
              <div className={`nav-item ${view === 'feedback' ? 'active' : ''}`} onClick={() => setView('feedback')}>
                <MessageSquare size={20} /> ผลประเมิน
              </div>
            </nav>

            <div style={{ marginTop: 'auto', padding: '16px 0', borderTop: '1px solid var(--border)' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '0 16px', marginBottom: 16 }}>
                <img src="<?php echo $_SESSION['admin_picture'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['admin_name']); ?>" style={{ width: 32, height: 32, borderRadius: '50%' }} />
                <div style={{ fontSize: 14, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                  <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </div>
              </div>
              <a href="api/logout.php" className="nav-item" style={{ textDecoration: 'none', color: 'var(--error)' }}>
                <Settings size={20} /> ออกจากระบบ
              </a>
            </div>
          </aside>

          <main className="main-content">
            {view === 'dashboard' && <DashboardView />}
            {view === 'staff' && <StaffView />}
            {view === 'feedback' && <FeedbackView />}
          </main>
        </div>
      );
    }



    const PerformanceChart = ({ data }) => {
      const chartRef = React.useRef(null);
      const chartInstance = React.useRef(null);

      React.useEffect(() => {
        if (chartRef.current && data) {
          if (chartInstance.current) {
            chartInstance.current.destroy();
          }

          const ctx = chartRef.current.getContext('2d');
          chartInstance.current = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: data.map(item => item.name),
              datasets: [{
                label: 'คะแนนเฉลี่ย',
                data: data.map(item => item.avg_rating || 0),
                backgroundColor: 'rgba(55, 125, 255, 0.8)',
                borderColor: 'rgba(55, 125, 255, 1)',
                borderWidth: 1,
                borderRadius: 8,
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { display: false },
                tooltip: {
                  callbacks: {
                    label: (context) => `คะแนนเฉลี่ย: ${Number(context.raw).toFixed(2)}`
                  }
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  max: 5,
                  ticks: { stepSize: 1 }
                },
                x: {
                  grid: { display: false }
                }
              }
            }
          });
        }
      }, [data]);

      return <canvas ref={chartRef} />;
    };

    function DashboardView() {
      const [stats, setStats] = useState(null);

      const fetchStats = () => {
        fetch('api/stats.php')
          .then(res => res.json())
          .then(data => setStats(data))
          .catch(err => console.error(err));
      };

      useEffect(() => { fetchStats(); }, []);

      const handleDeleteFeedback = (id) => {
        if (confirm('ยืนยันการลบผลประเมินนี้?')) {
          fetch(`api/feedbacks.php?id=${id}`, { method: 'DELETE' })
            .then(() => fetchStats());
        }
      };

      if (!stats) return <div style={{ padding: 48, textAlign: 'center', color: 'var(--muted)' }}>กำลังโหลดสถิติ...</div>;

      return (
        <div>
          <h1>แดชบอร์ด</h1>
          <p style={{ color: 'var(--muted)', marginTop: 8 }}>ภาพรวมข้อมูลของระบบ</p>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: 24, marginTop: 32 }}>
            <div className="card">
              <div style={{ color: 'var(--muted)', fontSize: 14, fontWeight: 500 }}>จำนวนพนักงานทั้งหมด</div>
              <div style={{ fontSize: 32, fontWeight: 700, marginTop: 8 }}>{stats.total_staff}</div>
            </div>
            <div className="card">
              <div style={{ color: 'var(--muted)', fontSize: 14, fontWeight: 500 }}>ผลประเมินทั้งหมด</div>
              <div style={{ fontSize: 32, fontWeight: 700, marginTop: 8, color: 'var(--primary)' }}>{stats.total_feedbacks}</div>
            </div>
            <div className="card">
              <div style={{ color: 'var(--muted)', fontSize: 14, fontWeight: 500 }}>คะแนนเฉลี่ย</div>
              <div style={{ fontSize: 32, fontWeight: 700, marginTop: 8, color: 'var(--secondary)' }}>{stats.avg_rating}</div>
            </div>
          </div>

          <div style={{ marginTop: 32 }}>
            <div className="card">
              <h2 style={{ fontSize: 18, marginBottom: 24 }}>เปรียบเทียบผลงานเจ้าหน้าที่ (คะแนนเฉลี่ย)</h2>
              <div style={{ height: 300 }}>
                <PerformanceChart data={stats.staff_performance} />
              </div>
            </div>
          </div>

          <h2 style={{ marginTop: 48, fontSize: 20 }}>ผลประเมินล่าสุด</h2>
          <div className="card" style={{ marginTop: 24, padding: 0 }}>
             <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left' }}>
                <thead>
                  <tr style={{ background: 'var(--bg)' }}>
                    <th style={{ padding: '16px 24px' }}>พนักงาน</th>
                    <th style={{ padding: '16px 24px' }}>คะแนน</th>
                    <th style={{ padding: '16px 24px' }}>ความคิดเห็น</th>
                    <th style={{ padding: '16px 24px' }}>เวลา</th>
                    <th style={{ padding: '16px 24px' }}>จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  {stats.recent_feedbacks.map(f => (
                    <tr key={f.id} style={{ borderBottom: '1px solid var(--border)' }}>
                      <td style={{ padding: '16px 24px', fontWeight: 500 }}>{f.staff_name}</td>
                      <td style={{ padding: '16px 24px' }}>
                        <span style={{ padding: '4px 10px', borderRadius: 20, background: 'var(--bg)', fontWeight: 700 }}>{f.rating}</span>
                      </td>
                      <td style={{ padding: '16px 24px', color: 'var(--muted)' }}>{f.feedback_text || '-'}</td>
                      <td style={{ padding: '16px 24px', fontSize: 13 }}>{new Date(f.created_at).toLocaleString('th-TH')}</td>
                      <td style={{ padding: '16px 24px' }}>
                        <button onClick={() => handleDeleteFeedback(f.id)} className="btn btn-ghost" style={{ padding: 8, color: 'var(--error)' }} title="ลบผลประเมิน">
                          <Trash2 size={16} />
                        </button>
                      </td>
                    </tr>
                  ))}
                  {stats.recent_feedbacks.length === 0 && (
                    <tr><td colSpan="5" style={{ padding: 32, textAlign: 'center', color: 'var(--muted)' }}>ไม่มีข้อมูล</td></tr>
                  )}
                </tbody>
              </table>
          </div>
        </div>
      );
    }

    function StaffView() {
      const [staffList, setStaffList] = useState([]);
      const [search, setSearch] = useState('');
      const [loading, setLoading] = useState(true);
      const [isModalOpen, setIsModalOpen] = useState(false);
      const [isQrModalOpen, setIsQrModalOpen] = useState(false);
      const [activeStaff, setActiveStaff] = useState(null);
      const [editingStaff, setEditingStaff] = useState(null);
      const [formData, setFormData] = useState({ staff_id: '', name: '', position: '', department: '', photo_url: '' });
      const [isSaving, setIsSaving] = useState(false);
      const [isUploading, setIsUploading] = useState(false);

      const fetchStaff = () => {
        setLoading(true);
        fetch('api/staff.php')
          .then(res => res.json())
          .then(data => {
            setStaffList(data);
            setLoading(false);
          })
          .catch(err => {
            console.error(err);
            setLoading(false);
          });
      };

      useEffect(() => { fetchStaff(); }, []);

      const getImageUrl = (url) => {
        if (!url) return 'https://ui-avatars.com/api/?name=Staff&background=random';
        if (url.startsWith('http')) return url;
        return '../' + url;
      };

      const handleOpenModal = (staff = null) => {
        if (staff) {
          setEditingStaff(staff);
          setFormData({ ...staff });
        } else {
          setEditingStaff(null);
          setFormData({ staff_id: '', name: '', position: '', department: '', photo_url: '' });
        }
        setIsModalOpen(true);
      };

      const handleOpenQr = (staff) => {
        setActiveStaff(staff);
        setIsQrModalOpen(true);
      };

      const handleSubmit = (e) => {
        e.preventDefault();
        setIsSaving(true);
        const method = editingStaff ? 'PUT' : 'POST';
        fetch('api/staff.php', {
          method: method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData)
        })
        .then(res => res.json())
        .then(res => {
          if (res.error) {
            alert('เกิดข้อผิดพลาด: ' + res.error);
          } else {
            setIsModalOpen(false);
            fetchStaff();
          }
        })
        .catch(err => alert('เกิดข้อผิดพลาดในการเชื่อมต่อ'))
        .finally(() => setIsSaving(false));
      };

      const handleDelete = (id) => {
        if (confirm('ยืนยันการลบพนักงาน?')) {
          fetch(`api/staff.php?id=${id}`, { method: 'DELETE' })
            .then(() => fetchStaff());
        }
      };

      const handlePhotoUpload = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        setIsUploading(true);
        const data = new FormData();
        data.append('photo', file);

        fetch('api/upload.php', { method: 'POST', body: data })
          .then(res => res.json())
          .then(res => {
            if (res.url) {
              setFormData({ ...formData, photo_url: res.url });
            } else if (res.error) {
              alert(res.error);
            }
          })
          .catch(() => alert('อัปโหลดรูปล้มเหลว'))
          .finally(() => setIsUploading(false));
      };

      const filteredStaff = staffList.filter(s => 
        (s.name || '').toLowerCase().includes(search.toLowerCase()) || 
        (s.staff_id || '').toLowerCase().includes(search.toLowerCase()) || 
        (s.position || '').toLowerCase().includes(search.toLowerCase())
      );

      return (
        <div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h1>พนักงาน</h1>
            <button className="btn btn-primary" onClick={() => handleOpenModal()}><Plus size={18} /> เพิ่มพนักงาน</button>
          </div>

          <div className="card" style={{ marginTop: 32, padding: 0 }}>
            <div style={{ padding: 24, borderBottom: '1px solid var(--border)', display: 'flex', gap: 16 }}>
              <div style={{ position: 'relative', flex: 1 }}>
                <Search size={18} style={{ position: 'absolute', left: 12, top: '50%', transform: 'translateY(-50%)', color: 'var(--muted)' }} />
                <input 
                  type="text" 
                  placeholder="ค้นหาชื่อพนักงาน หรือรหัส..." 
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  style={{ width: '100%', padding: '12px 12px 12px 40px', borderRadius: 'var(--radius-md)', border: '1px solid var(--border)', outline: 'none' }} 
                />
              </div>
            </div>
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left' }}>
                <thead>
                  <tr style={{ background: 'var(--bg)' }}>
                    <th style={{ padding: '16px 24px' }}>พนักงาน</th>
                    <th style={{ padding: '16px 24px' }}>ตำแหน่ง / แผนก</th>
                    <th style={{ padding: '16px 24px' }}>รหัส</th>
                    <th style={{ padding: '16px 24px' }}>เครื่องมือ</th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr><td colSpan="4" style={{ padding: 48, textAlign: 'center', color: 'var(--muted)' }}>กำลังโหลดข้อมูล...</td></tr>
                  ) : filteredStaff.map(s => (
                    <tr key={s.id} style={{ borderBottom: '1px solid var(--border)' }}>
                      <td style={{ padding: '16px 24px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                          <img src={getImageUrl(s.photo_url)} style={{ width: 40, height: 40, borderRadius: '50%', objectFit: 'cover' }} />
                          <div style={{ fontWeight: 600 }}>{s.name}</div>
                        </div>
                      </td>
                      <td style={{ padding: '16px 24px' }}>
                        <div style={{ fontWeight: 500 }}>{s.position}</div>
                        <div style={{ fontSize: 12, color: 'var(--muted)' }}>{s.department}</div>
                      </td>
                      <td style={{ padding: '16px 24px', fontFamily: 'monospace' }}>{s.staff_id}</td>
                      <td style={{ padding: '16px 24px' }}>
                        <div style={{ display: 'flex', gap: 8 }}>
                          <button onClick={() => handleOpenQr(s)} className="btn btn-ghost" style={{ padding: 8, color: 'var(--primary)' }}><QrCode size={16} /></button>
                          <button onClick={() => handleOpenModal(s)} className="btn btn-ghost" style={{ padding: 8 }}><Edit2 size={16} /></button>
                          <button onClick={() => handleDelete(s.id)} className="btn btn-ghost" style={{ padding: 8, color: 'var(--error)' }}><Trash2 size={16} /></button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {!loading && filteredStaff.length === 0 && (
                    <tr><td colSpan="4" style={{ padding: 48, textAlign: 'center', color: 'var(--muted)' }}>ไม่พบข้อมูลพนักงาน</td></tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>

          <Modal title={editingStaff ? 'แก้ไขข้อมูลพนักงาน' : 'เพิ่มพนักงานใหม่'} isOpen={isModalOpen} onClose={() => setIsModalOpen(false)}>
            <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
              <div style={{ display: 'flex', justifyContent: 'center', marginBottom: 8 }}>
                <div style={{ position: 'relative', width: 100, height: 100 }}>
                  <img src={getImageUrl(formData.photo_url)} style={{ width: '100%', height: '100%', borderRadius: '50%', objectFit: 'cover', border: '2px solid var(--border)', opacity: isUploading ? 0.5 : 1 }} />
                  {isUploading && <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 10, fontWeight: 700, color: 'var(--primary)' }}>กำลังโหลด...</div>}
                  <label style={{ position: 'absolute', bottom: 0, right: 0, background: 'var(--primary)', color: 'white', padding: 6, borderRadius: '50%', cursor: 'pointer', display: isUploading ? 'none' : 'flex' }}>
                    <Camera size={14} />
                    <input type="file" onChange={handlePhotoUpload} style={{ display: 'none' }} accept="image/*" />
                  </label>
                </div>
              </div>
              <div className="form-group">
                <label style={{ display: 'block', fontSize: 13, fontWeight: 600, marginBottom: 6 }}>รหัสพนักงาน</label>
                <input required value={formData.staff_id} onChange={e => setFormData({...formData, staff_id: e.target.value})} style={{ width: '100%', padding: 12, borderRadius: 8, border: '1px solid var(--border)' }} placeholder="เช่น staff001" />
              </div>
              <div className="form-group">
                <label style={{ display: 'block', fontSize: 13, fontWeight: 600, marginBottom: 6 }}>ชื่อ-นามสกุล</label>
                <input required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} style={{ width: '100%', padding: 12, borderRadius: 8, border: '1px solid var(--border)' }} placeholder="ระบุชื่อจริง-นามสกุล" />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div className="form-group">
                  <label style={{ display: 'block', fontSize: 13, fontWeight: 600, marginBottom: 6 }}>ตำแหน่ง</label>
                  <input value={formData.position} onChange={e => setFormData({...formData, position: e.target.value})} style={{ width: '100%', padding: 12, borderRadius: 8, border: '1px solid var(--border)' }} />
                </div>
                <div className="form-group">
                  <label style={{ display: 'block', fontSize: 13, fontWeight: 600, marginBottom: 6 }}>แผนก</label>
                  <input value={formData.department} onChange={e => setFormData({...formData, department: e.target.value})} style={{ width: '100%', padding: 12, borderRadius: 8, border: '1px solid var(--border)' }} />
                </div>
              </div>
              <button type="submit" className="btn btn-primary" style={{ marginTop: 8, justifyContent: 'center' }} disabled={isSaving || isUploading}>
                {isSaving ? 'กำลังบันทึก...' : (editingStaff ? 'บันทึกการแก้ไข' : 'บันทึกพนักงาน')}
              </button>
            </form>
          </Modal>

          <QrModal staff={activeStaff} isOpen={isQrModalOpen} onClose={() => setIsQrModalOpen(false)} />
        </div>
      );
    }

    function FeedbackView() {
      const [feedbacks, setFeedbacks] = useState([]);
      const [loading, setLoading] = useState(true);
      const [search, setSearch] = useState('');

      const fetchFeedbacks = () => {
        setLoading(true);
        fetch('api/feedbacks.php')
          .then(res => res.json())
          .then(data => {
            setFeedbacks(data);
            setLoading(false);
          });
      };

      useEffect(() => { fetchFeedbacks(); }, []);

      const handleDelete = (id) => {
        if (confirm('ยืนยันการลบผลประเมินนี้?')) {
          fetch(`api/feedbacks.php?id=${id}`, { method: 'DELETE' })
            .then(() => fetchFeedbacks());
        }
      };

      const getRatingEmoji = (rating) => {
        const ratings = {
          5: "😍",
          4: "😊",
          3: "😐",
          2: "😕",
          1: "😠"
        };
        return ratings[rating] || "❓";
      };

      const exportToExcel = () => {
        const worksheet = XLSX.utils.json_to_sheet(feedbacks);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Feedbacks");
        XLSX.writeFile(workbook, `Staff_Feedbacks_${new Date().toISOString().slice(0,10)}.xlsx`);
      };

      const exportToPDF = () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Staff Feedback Report", 14, 15);
        const tableData = feedbacks.map(f => [
          f.staff_name,
          f.rating,
          f.feedback_text || '-',
          new Date(f.created_at).toLocaleString('th-TH')
        ]);
        doc.autoTable({
          head: [['Staff Name', 'Rating', 'Comment', 'Date']],
          body: tableData,
          startY: 20,
        });
        doc.save(`Staff_Feedbacks_${new Date().toISOString().slice(0,10)}.pdf`);
      };

      const filteredFeedbacks = feedbacks.filter(f => 
        (f.staff_name || '').toLowerCase().includes(search.toLowerCase()) || 
        (f.staff_id || '').toLowerCase().includes(search.toLowerCase()) ||
        (f.feedback_text || '').toLowerCase().includes(search.toLowerCase())
      );

      return (
        <div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h1>ผลประเมิน</h1>
            <div style={{ display: 'flex', gap: 12 }}>
              <button className="btn btn-ghost" onClick={exportToExcel}><Download size={18} /> Excel</button>
              <button className="btn btn-ghost" onClick={exportToPDF}><QrCode size={18} /> PDF</button>
            </div>
          </div>

          <div className="card" style={{ marginTop: 32, padding: 0 }}>
            <div style={{ padding: 24, borderBottom: '1px solid var(--border)', display: 'flex', gap: 16 }}>
              <div style={{ position: 'relative', flex: 1 }}>
                <Search size={18} style={{ position: 'absolute', left: 12, top: '50%', transform: 'translateY(-50%)', color: 'var(--muted)' }} />
                <input 
                  type="text" 
                  placeholder="ค้นหาชื่อพนักงาน, รหัส หรือความคิดเห็น..." 
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  style={{ width: '100%', padding: '12px 12px 12px 40px', borderRadius: 'var(--radius-md)', border: '1px solid var(--border)', outline: 'none' }} 
                />
              </div>
            </div>
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left' }}>
                <thead>
                  <tr style={{ background: 'var(--bg)' }}>
                    <th style={{ padding: '16px 24px' }}>พนักงาน</th>
                    <th style={{ padding: '16px 24px' }}>ระดับความพึงพอใจ</th>
                    <th style={{ padding: '16px 24px' }}>ความคิดเห็น</th>
                    <th style={{ padding: '16px 24px' }}>เวลา</th>
                    <th style={{ padding: '16px 24px' }}>จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr><td colSpan="5" style={{ padding: 48, textAlign: 'center', color: 'var(--muted)' }}>กำลังโหลดข้อมูล...</td></tr>
                  ) : filteredFeedbacks.map(f => (
                    <tr key={f.id} style={{ borderBottom: '1px solid var(--border)' }}>
                      <td style={{ padding: '16px 24px' }}>
                        <div style={{ fontWeight: 600 }}>{f.staff_name}</div>
                        <div style={{ fontSize: 12, color: 'var(--muted)' }}>{f.staff_id}</div>
                      </td>
                      <td style={{ padding: '16px 24px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                          <span style={{ fontSize: 24 }}>{getRatingEmoji(f.rating)}</span>
                          <span style={{ padding: '4px 10px', borderRadius: 20, background: 'var(--bg)', fontWeight: 700 }}>{f.rating}</span>
                        </div>
                      </td>
                      <td style={{ padding: '16px 24px', color: 'var(--muted)', maxWidth: 300, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {f.feedback_text || '-'}
                      </td>
                      <td style={{ padding: '16px 24px', fontSize: 13 }}>{new Date(f.created_at).toLocaleString('th-TH')}</td>
                      <td style={{ padding: '16px 24px' }}>
                        <button onClick={() => handleDelete(f.id)} className="btn btn-ghost" style={{ padding: 8, color: 'var(--error)' }} title="ลบผลประเมิน">
                          <Trash2 size={16} />
                        </button>
                      </td>
                    </tr>
                  ))}
                  {!loading && filteredFeedbacks.length === 0 && (
                    <tr><td colSpan="5" style={{ padding: 48, textAlign: 'center', color: 'var(--muted)' }}>ไม่พบข้อมูลผลประเมิน</td></tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      );
    }

    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
  </script>
</body>
</html>
