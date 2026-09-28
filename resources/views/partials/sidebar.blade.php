<aside class="sidebar">
    <h2>CRM Center</h2>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.index') ? 'active' : '' }}">
        <span class="nav-icon">👥</span> ลูกค้า
    </a>
   
    <details class="submenu-group" {{ request()->routeIs('sales.*') ? 'open' : '' }}>
        <summary class="submenu-title-button">
            <span class="submenu-label">
                <span class="nav-icon">🧾</span>
                <span class="submenu-title">การขาย</span>
            </span>
            <span class="submenu-caret">▾</span>
        </summary>
        <ul class="submenu-list">
            <li>
                <a href="{{ route('sales.overview') }}" class="submenu-link {{ request()->routeIs('sales.overview') ? 'active' : '' }}">
                    <span class="nav-icon">🏆</span> ภาพรวมการขาย
                </a>
            </li>
            <li>
                <a href="{{ route('sales.orders') }}" class="submenu-link {{ request()->routeIs('sales.orders') ? 'active' : '' }}">
                    <span class="nav-icon">➕</span> เพิ่มการขาย
                </a>
            </li>
        </ul>
    </details>
    <!-- <a href="{{ route('sale-returns.index') }}" class="{{ request()->routeIs('sale-returns.*') ? 'active' : '' }}">
        <span class="nav-icon">↩️</span> คืนสินค้า
    </a> -->
    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
        <span class="nav-icon">📦</span> สินค้า
    </a>
   
    <a href="{{ route('calls.index') }}" class="{{ request()->routeIs('calls.index') ? 'active' : '' }}">
        <span class="nav-icon">📞</span> การโทร
    </a>
    <a href="{{ route('campaigns.index') }}" class="{{ request()->routeIs('campaigns.index') ? 'active' : '' }}">
        <span class="nav-icon">📢</span> แคมเปญ
    </a>
     <a href="{{ route('salesmen.index') }}" class="{{ request()->routeIs('salesmen.*') ? 'active' : '' }}">
        <span class="nav-icon">💼</span> พนักงานขาย
    </a>
    <a href="{{ route('sales.dashboard') }}" class="{{ request()->routeIs('sales.dashboard') ? 'active' : '' }}">
        <span class="nav-icon">📈</span> Dashboard พนักงานขาย
    </a>
    <details class="submenu-group" {{ request()->routeIs(['settings.index', 'users.index', 'assignments.*']) ? 'open' : '' }}>
        <summary class="submenu-title-button">
            <span class="submenu-label">
                <span class="nav-icon">⚙️</span>
                <span class="submenu-title">การตั้งค่า</span>
            </span>
            <span class="submenu-caret">▾</span>
        </summary>
        <ul class="submenu-list">
            <li>
                <a href="{{ route('settings.index') }}" class="submenu-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span> การตั้งค่า
                </a>
            </li>
            <li>
                <a href="{{ route('users.index') }}" class="submenu-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="nav-icon">👤</span> จัดการผู้ใช้
                </a>
            </li>
            <li>
                <a href="{{ route('assignments.index') }}" class="submenu-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                    <span class="nav-icon">📝</span> จัดการมอบหมายลูกค้า
                </a>
            </li>
        </ul>
    </details>
</aside>
