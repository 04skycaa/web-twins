<div class="coming-soon-container">
    <div class="coming-soon-card">
        <div class="icon-wrapper">
            <iconify-icon icon="{{ $icon ?? 'solar:settings-bold-duotone' }}"></iconify-icon>
        </div>
        <h2>{{ $title }}</h2>
        <p>{{ $description ?? 'Halaman ini sedang dalam pengembangan. Segera hadir fitur lengkap untuk mempermudah operasional bisnis Anda.' }}</p>
        
        <div class="progress-bar-wrapper">
            <div class="progress-bar"></div>
        </div>
        <span class="status-text">Dalam Pengembangan</span>
    </div>
</div>

<style>
    .coming-soon-container {
        width: 100%;
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .coming-soon-card {
        background: #ffffff;
        width: 100%;
        max-width: 800px;
        padding: 60px 40px;
        border-radius: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.02);
        animation: cardFadeIn 0.8s ease-out;
    }

    .icon-wrapper {
        width: 100px;
        height: 100px;
        background: #f8fafc;
        border-radius: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        font-size: 50px;
        color: #64748b;
        box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .coming-soon-card h2 {
        font-size: 28px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 15px;
        letter-spacing: -0.5px;
    }

    .coming-soon-card p {
        font-size: 16px;
        color: #64748b;
        line-height: 1.6;
        max-width: 600px;
        margin: 0 auto 40px;
    }

    .progress-bar-wrapper {
        width: 100%;
        max-width: 300px;
        height: 8px;
        background: #f1f5f9;
        border-radius: 10px;
        margin: 0 auto 15px;
        overflow: hidden;
    }

    .progress-bar {
        width: 65%;
        height: 100%;
        background: linear-gradient(90deg, #0ea5e9, #6366f1);
        border-radius: 10px;
        position: relative;
        animation: progressAnim 2s ease-in-out infinite alternate;
    }

    .status-text {
        font-size: 13px;
        font-weight: 700;
        color: #0ea5e9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes progressAnim {
        from { filter: hue-rotate(0deg); width: 60%; }
        to { filter: hue-rotate(45deg); width: 75%; }
    }
</style>
