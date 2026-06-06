<style>
.auth-page {
  min-height: calc(100vh - 64px);
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #F0FDF7 0%, #EFF6FF 50%, #FAF5FF 100%);
  padding: 40px 20px;
}
.auth-container { width: 100%; max-width: 440px; margin: 0 auto; }
.auth-card {
  background: #fff;
  border-radius: var(--radius-xl);
  padding: 40px;
  box-shadow: var(--shadow-xl);
  border: 1px solid rgba(255,255,255,.8);
}
.auth-title {
  font-family: var(--font-display);
  font-size: 22px;
  font-weight: 800;
  color: var(--text-dark);
  margin: 0 0 24px;
}
.role-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--primary-light);
  color: var(--primary-dark);
  border: 1px solid rgba(0,193,106,0.25);
  border-radius: 999px;
  padding: 5px 14px;
  font-size: 13px;
  font-weight: 600;
}
.input-icon-wrap { position: relative; }
.input-icon {
  position: absolute; left: 12px; top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted); font-size: 13px; pointer-events: none;
}
.input-with-icon { padding-left: 38px !important; }
.toggle-pass-btn {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted); font-size: 14px;
  background: none; border: none; cursor: pointer; padding: 4px;
  transition: var(--transition);
}
.toggle-pass-btn:hover { color: var(--text-secondary); }
.auth-divider { position: relative; text-align: center; margin: 20px 0; }
.auth-divider::before {
  content: ''; position: absolute; top: 50%; left: 0; right: 0;
  height: 1px; background: var(--border);
}
.auth-divider span {
  position: relative; background: #fff;
  padding: 0 14px; font-size: 12px; color: var(--text-muted);
}
.btn-google {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  background: #fff; border: 1.5px solid var(--border); color: #3C4043;
  font-size: 14px; font-weight: 600; padding: 10px 16px;
  border-radius: var(--radius-sm); transition: var(--transition); text-decoration: none;
  font-family: var(--font-body);
}
.btn-google:hover {
  background: var(--bg-soft); border-color: var(--border-dark);
  box-shadow: var(--shadow-sm); color: #3C4043;
}
.password-strength {
  height: 4px; border-radius: 2px; background: var(--border);
  overflow: hidden; transition: all .3s;
}
.password-strength::after {
  content: ''; display: block; height: 100%;
  width: var(--strength, 0%); background: var(--strength-color, var(--border));
  transition: width .3s, background .3s; border-radius: 2px;
}
</style>
