<template>
    <div class="landing">
        <div class="gradient-bg"></div>
        <div class="gradient-bg2"></div>
        
        <div class="hero">
            <h1 class="title">Управляйте деньгами<br>как профи.</h1>
            <p class="subtitle">
                Автоматизируйте бюджет, планируйте финансовые цели и анализируйте расходы с помощью передовых технологий ФинУчёт.
            </p>
            <router-link :to="{ name: 'app.register' }" class="cta-button">
                НАЧАТЬ БЕСПЛАТНО
            </router-link>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Аналитика расходов</h3>
                <p>Детальный анализ всех транзакций с наглядными графиками</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Финансовые цели</h3>
                <p>Ставьте цели и отслеживайте прогресс их достижения</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Умные бюджеты</h3>
                <p>Устанавливайте лимиты по категориям и контролируйте расходы</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔄</div>
                <h3>Переводы между счетами</h3>
                <p>Легко перемещайте средства между своими счетами</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Main',

}
</script>

<style scoped>
    .landing {
        min-height: 100vh;
        background: #F8FAFC;
        overflow-x: hidden;
    }

    .gradient-bg {
        width: 800px;
        height: 800px;
        background: radial-gradient(circle, rgba(0, 212, 255, 0.1) 0%, rgba(190, 94, 237, 0) 70%);
        position: fixed;
        top: -300px;
        right: -200px;
        border-radius: 50%;
        pointer-events: none;
    }

    .gradient-bg2 {
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(190, 94, 237, 0.08) 0%, rgba(0, 212, 255, 0) 70%);
        position: fixed;
        bottom: -200px;
        left: -200px;
        border-radius: 50%;
        pointer-events: none;
    }

    .hero {
        text-align: center;
        padding: 160px 40px 100px;
        position: relative;
        z-index: 1;
    }

    .title {
        font-size: 64px;
        font-family: 'Inter', sans-serif;
        font-weight: bold;
        background: linear-gradient(135deg, #0F172A 0%, #475569 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        letter-spacing: -2px;
        margin-bottom: 24px;
    }

    .subtitle {
        font-size: 18px;
        font-family: 'Inter', sans-serif;
        color: #475569;
        line-height: 1.6;
        margin-bottom: 40px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-button {
        display: inline-block;
        padding: 16px 40px;
        background: linear-gradient(135deg, #00D4FF 0%, #BE5EED 100%);
        border-radius: 12px;
        font-family: 'Inter', sans-serif;
        font-weight: bold;
        font-size: 16px;
        color: white;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.2);
    }

    .cta-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 212, 255, 0.3);
    }

    .features {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 32px;
        max-width: 1200px;
        margin: 60px auto 80px;
        padding: 0 40px;
        position: relative;
        z-index: 1;
    }

    .feature-card {
        background: white;
        backdrop-filter: blur(24px);
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 32px 24px;
        text-align: center;
        transition: all 0.2s ease;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .feature-card:hover {
        border-color: #00D4FF;
        transform: translateY(-4px);
        box-shadow: 0 20px 30px -10px rgba(0, 212, 255, 0.15);
    }

    .feature-icon {
        font-size: 48px;
        margin-bottom: 20px;
    }

    .feature-card h3 {
        font-family: 'Inter', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #0F172A;
        margin-bottom: 12px;
    }

    .feature-card p {
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: #64748B;
        line-height: 1.5;
    }

    @media (max-width: 1024px) {
        .title {
            font-size: 48px;
        }
        .features {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .features {
            grid-template-columns: 1fr;
        }
        .title {
            font-size: 36px;
        }
        .hero {
            padding: 120px 20px 60px;
        }
    }
</style>