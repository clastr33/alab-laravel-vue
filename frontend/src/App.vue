<template>
  <div class="app">
    <header class="topbar">
      <div class="topbarInner">
        <RouterLink to="/" class="brand" aria-label="ALAB">
          <span class="brandMark" aria-hidden="true"></span>
          <span class="brandText">
            <span class="brandName">ALAB</span>
            <span class="brandTag">laboratoria</span>
          </span>
        </RouterLink>

        <nav class="nav" aria-label="Główna nawigacja">
          <RouterLink to="/results">Odbierz wyniki</RouterLink>
        </nav>

        <div class="actions" aria-label="Akcje">
          <button class="iconBtn" type="button" aria-label="Szukaj">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path
                d="M10.5 3a7.5 7.5 0 1 1 0 15A7.5 7.5 0 0 1 10.5 3Zm0 2a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11Zm8.65 12.24 1.61 1.61a1 1 0 0 1-1.42 1.42l-1.61-1.61a1 1 0 0 1 1.42-1.42Z"
              />
            </svg>
          </button>
          <RouterLink class="iconBtn" to="/login" aria-label="Konto">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path
                d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.51 4.51 0 0 0 12 12Zm0 2c-4.42 0-8 2.24-8 5a1 1 0 0 0 2 0c0-1.5 2.58-3 6-3s6 1.5 6 3a1 1 0 0 0 2 0c0-2.76-3.58-5-8-5Z"
              />
            </svg>
          </RouterLink>
        </div>
      </div>
    </header>

    <main class="page">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.app {
  min-height: 100vh;
  background: #f6f8fb;
  color: #0b1f3b;
}
.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: #fff;
  border-bottom: 1px solid #e6edf6;
}
.topbarInner {
  height: 72px;
  display: grid;
  grid-template-columns: 240px 1fr 120px;
  align-items: center;
  gap: 16px;
  max-width: 1120px;
  margin: 0 auto;
  padding: 0 20px;
}
.brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
  min-width: 0;
}
.brandMark {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: radial-gradient(circle at 30% 30%, #55b4ff, #2b77ff);
  box-shadow: 0 6px 14px rgba(43, 119, 255, 0.22);
}
.brandText {
  display: grid;
  line-height: 1.05;
}
.brandName {
  font-weight: 800;
  letter-spacing: -0.02em;
  font-size: 22px;
  color: #0b2f66;
}
.brandTag {
  font-style: italic;
  font-weight: 600;
  color: #2d8be6;
  font-size: 15px;
}

.nav {
  display: flex;
  justify-content: center;
  gap: 22px;
  min-width: 0;
}
.nav :deep(a) {
  color: #123a6d;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  padding: 10px 2px;
  border-bottom: 2px solid transparent;
  white-space: nowrap;
}
.nav :deep(a.router-link-active) {
  border-bottom-color: #123a6d;
}
.nav :deep(a:hover) {
  color: #0b2f66;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
.iconBtn {
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  border: 1px solid #e6edf6;
  background: #fff;
  color: #123a6d;
  cursor: pointer;
  text-decoration: none;
}
.iconBtn svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}
.iconBtn:hover {
  background: #f2f6fc;
}

.page {
  max-width: 1120px;
  margin: 0 auto;
  padding: 22px 20px 72px;
}

@media (max-width: 980px) {
  .topbarInner {
    grid-template-columns: 1fr 100px;
  }
  .nav {
    display: none;
  }
}
</style>
