<template>
  <div class="not-found-page">
    <div class="container">
      <div class="error-content">
        <h1 class="error-code">404</h1>
        
        <h2 class="error-title">Упс! Страница не найдена</h2>
        
        <p class="error-text">
          Похоже, вы заблудились в лабиринте знаний. <br>
          Такой страницы не существует или она была перемещена.
        </p>

        <div class="actions">
          <a href="/" class="btn-primary">Вернуться на главную</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFoundPage'
}
</script>

<style scoped>
  .illustration,.not-found-page{position:relative;display:flex}.not-found-page{min-height:100vh;align-items:center;justify-content:center;background:#fff;padding:40px 20px;overflow:hidden}.container{max-width:800px;width:100%;text-align:center;z-index:2}.error-content{display:flex;flex-direction:column;align-items:center}.error-code{font-size:120px;line-height:1;margin:0;background:linear-gradient(135deg,#0b1c4f 0,#0e6cf7 100%);-webkit-background-clip:text;background-clip:text;color:transparent;font-weight:900;letter-spacing:-5px;animation:6s ease-in-out infinite float}.error-title{font-size:36px;color:#0b1c4f;margin:20px 0 15px;font-weight:700}.error-text{font-size:18px;color:#828386;margin-bottom:40px;line-height:1.6;max-width:500px}.btn-primary,.btn-secondary{padding:15px 40px;border-radius:30px;text-decoration:none;font-weight:600;font-size:16px}.actions{display:flex;gap:20px;margin-bottom:60px;flex-wrap:wrap;justify-content:center}.btn-primary{background:linear-gradient(90deg,#0b1c4f 0,#0e6cf7 100%);color:#fff;transition:transform .2s,box-shadow .2s;box-shadow:0 10px 20px rgba(14,108,247,.3)}.btn-primary:hover{transform:translateY(-3px);box-shadow:0 15px 30px rgba(14,108,247,.4)}.btn-secondary{background:#f6f8fb;color:#0b1c4f;border:2px solid #e0e5ec;transition:.2s}.btn-secondary:hover{border-color:#0e6cf7;color:#0e6cf7;transform:translateY(-3px)}.illustration{width:200px;height:200px;justify-content:center;align-items:center}.illustration img{width:100px;height:auto;z-index:2;filter:drop-shadow(0 10px 20px rgba(11, 28, 79, .2))}.floating-shape{position:absolute;border-radius:50%;opacity:.6;animation:8s ease-in-out infinite floatShape}.shape-1{width:60px;height:60px;background:rgba(14,108,247,.2);top:0;left:0;animation-delay:0s}.shape-2{width:40px;height:40px;background:rgba(11,28,79,.15);bottom:20px;right:10px;animation-delay:2s}.shape-3{width:30px;height:30px;background:rgba(124,176,252,.4);top:50%;right:-20px;animation-delay:4s}@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}@keyframes floatShape{0%,100%{transform:translate(0,0)}50%{transform:translate(10px,-15px)}}@media (max-width:768px){.error-code{font-size:80px}.error-title{font-size:28px}.actions{flex-direction:column;width:100%}.btn-primary,.btn-secondary{width:100%;box-sizing:border-box}}
</style>