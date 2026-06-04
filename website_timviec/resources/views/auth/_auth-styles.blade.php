<style>
.auth-page {
  min-height: calc(100vh - 60px);
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #f0fdf7 0%, #e8f4fd 100%);
  padding: 40px 16px;
}
.auth-container { width: 100%; max-width: 420px; margin: 0 auto; }
.auth-card { box-shadow: var(--shadow-lg) !important; }
.auth-title { font-size: 20px; font-weight: 800; margin-bottom: 24px; color: var(--secondary); }

.role-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--primary-light);
  color: var(--primary);
  border: 1px solid rgba(16,185,129,.25);
  border-radius: 20px;
  padding: 5px 14px;
  font-size: 13px;
  font-weight: 600;
}

/* Input icon */
.input-icon-wrap { position: relative; }
.input-icon {
  position: absolute; left: 12px; top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted); font-size: 13px; pointer-events: none;
}
.input-with-icon { padding-left: 36px !important; }
.toggle-pass-btn {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted); font-size: 14px;
  background: none; border: none; cursor: pointer; padding: 4px;
}

/* Divider */
.auth-divider { position: relative; text-align: center; margin: 16px 0; }
.auth-divider::before {
  content: ''; position: absolute; top: 50%; left: 0; right: 0;
  height: 1px; background: var(--border);
}
.auth-divider span {
  position: relative; background: #fff;
  padding: 0 14px; font-size: 12px; color: var(--text-muted);
}

/* Google button */
.btn-google {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  background: #fff; border: 1.5px solid #dadce0; color: #3c4043;
  font-size: 14px; font-weight: 500; padding: 10px 16px;
  border-radius: var(--radius); transition: var(--transition); text-decoration: none;
}
.btn-google:hover {
  background: #f8f9fa; border-color: #c6c9cc;
  box-shadow: 0 1px 4px rgba(0,0,0,.12); color: #3c4043; text-decoration: none;
}

/* Password strength */
.password-strength {
  height: 4px; border-radius: 2px; background: #e9ecef;
  overflow: hidden; transition: all .3s;
}
.password-strength::after {
  content: ''; display: block; height: 100%;
  width: var(--strength, 0%); background: var(--strength-color, #e9ecef);
  transition: width .3s, background .3s;
  border-radius: 2px;
}
.fs-11 { font-size: 11px; }
</style>
