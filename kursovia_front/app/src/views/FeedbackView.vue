<template>
  <div class="feedback-page">
    <div class="container">
      <div class="page-header">
        <h1>Обратная связь</h1>
        <h5>Есть вопросы или предложения? Напишите нам!</h5>
      </div>
      <div class="feedback-wrapper">
        <div class="form-section">
          <div class="card">
            <h3>Оставить отзыв</h3>
            <form @submit.prevent="submitFeedback">
              
              <div class="form-group">
                <label>Ваше имя</label>
                <input type="text" v-model="form.name" placeholder="Иван Иванов" required>
              </div>

              <div class="form-group">
                <label>Email для связи</label>
                <input type="email" v-model="form.email" placeholder="ivan@example.com" required>
              </div>

              <div class="form-group">
                <label>Оценка</label>
                <div class="rating-input">
                  <span 
                    v-for="star in 5" 
                    :key="star" 
                    class="star" 
                    :class="{ active: star <= form.rating }"
                    @click="form.rating = star"
                  >★</span>
                </div>
              </div>

              <div class="form-group">
                <label>Сообщение</label>
                <textarea v-model="form.message" rows="5" placeholder="Расскажите о вашем опыте..." required></textarea>
              </div>

              <button type="submit" class="btn-submit" :disabled="isLoading">
                {{ isLoading ? 'Отправка...' : 'Отправить отзыв' }}
              </button>
              
              <p v-if="successMessage" class="success-msg">{{ successMessage }}</p>
              <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
            </form>
          </div>
        </div>

        <!-- Правая колонка: Список отзывов -->
        <div class="reviews-section">
          <h3>Отзывы студентов</h3>
          
          <div v-if="loadingReviews" class="loader">Загрузка отзывов...</div>
          
          <div v-else-if="reviews.length === 0" class="no-reviews">
            Пока нет отзывов. Будьте первыми!
          </div>

          <div v-else class="reviews-list">
            <div v-for="review in reviews" :key="review.id" class="review-card">
              <div class="review-header">
                <div class="reviewer-info">
                  <h4>{{ review.name }}</h4>
                  <span class="review-date">{{ formatDate(review.created_at) }}</span>
                </div>
                <div class="review-rating">
                  <span v-for="n in 5" :key="n" :class="{ active: n <= review.rating }">★</span>
                </div>
              </div>
              <p class="review-text">{{ review.message }}</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FeedbackPage',
  data() {
    return {
      form: {
        name: '',
        email: '',
        message: '',
        rating: 5
      },
      isLoading: false,
      successMessage: '',
      errorMessage: '',
      reviews: [],
      loadingReviews: false
    };
  },
  mounted() {
    this.loadReviews();
  },
  methods: {
    async loadReviews() {
      this.loadingReviews = true;
      try {
        const response = await fetch(`${process.env.VUE_APP_API_BASE_URL}/feedback`);
        if (response.ok) {
          this.reviews = await response.json();
        }
      } catch (error) {
        console.error('Ошибка загрузки отзывов:', error);
      } finally {
        this.loadingReviews = false;
      }
    },

    async submitFeedback() {
      this.isLoading = true;
      this.successMessage = '';
      this.errorMessage = '';

      try {
        const headers = new Headers();
        headers.append('Content-Type', 'application/json');

        const response = await fetch(`${process.env.VUE_APP_API_BASE_URL}/feedback`, {
          method: 'POST',
          headers: headers,
          body: JSON.stringify(this.form)
        });

        const result = await response.json();

        if (response.ok) {
          this.successMessage = result.message;
          const newReview = result.data; 
          this.reviews.unshift(newReview);
          this.form = { name: '', email: '', message: '', rating: 5 };
          setTimeout(() => {
            this.successMessage = '';
          }, 5000);
        } else {
          this.errorMessage = result.message || 'Ошибка при отправке';
          if (result.errors) {
            const errors = Object.values(result.errors).flat();
            this.errorMessage = errors.join('\n');
          }
        }
      } catch (error) {
        console.error(error);
        this.errorMessage = 'Ошибка сети. Попробуйте позже.';
      } finally {
        this.isLoading = false;
      }
    },

    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('ru-RU', options);
    }
  }
};
</script>

<style scoped>
  .feedback-page{padding:60px 0;min-height:100vh;background:#fff}.container{max-width:1200px;margin:0 auto;padding:0 20px}.error-msg,.success-msg{margin-top:15px;text-align:center}.page-header{text-align:center;margin-bottom:50px}.page-header h1{font-size:48px;background:linear-gradient(90deg,#0b1c4f 0,#0e6cf7 100%);-webkit-background-clip:text;background-clip:text;color:transparent;margin-bottom:10px}.page-header h5{color:#828386;font-size:20px}.feedback-wrapper{display:grid;grid-template-columns:1fr 1fr;gap:40px}.card{background:#f6f8fb;padding:40px;border-radius:30px;box-shadow:0 10px 30px rgba(0,0,0,.05)}.card h3,.reviews-section h3{color:#0b1c4f;font-size:24px;margin-bottom:25px}.form-group{margin-bottom:20px}.form-group label{display:block;color:#0b1c4f;font-weight:600;margin-bottom:8px;font-size:14px}.form-group input,.form-group textarea{width:100%;padding:12px 20px;border-radius:20px;border:1px solid #e0e5ec;background:#fff;font-family:inherit;font-size:15px;outline:0;transition:border-color .2s;box-sizing:border-box}.form-group input:focus,.form-group textarea:focus{border-color:#0e6cf7}.rating-input{display:flex;gap:5px;font-size:30px;cursor:pointer;color:#e0e5ec}.review-rating .active,.star.active{color:gold}.review-rating{color:#e0e5ec;font-size:18px}.btn-submit{width:100%;padding:15px;background:linear-gradient(90deg,#0b1c4f 0,#0e6cf7 100%);color:#fff;border:none;border-radius:30px;font-size:16px;font-weight:600;cursor:pointer;transition:transform .2s}.btn-submit:hover:not(:disabled){transform:translateY(-3px)}.btn-submit:disabled{opacity:.7;cursor:not-allowed}.success-msg{color:#208c05;font-weight:600}.error-msg{color:#b20606;font-size:14px}.reviews-list{display:flex;flex-direction:column;gap:20px;max-height:600px;overflow-y:auto;padding-right:10px}.review-card{background:#fff;padding:20px;border-radius:20px;border:1px solid #f0f0f0;transition:box-shadow .2s}.review-card:hover{box-shadow:0 5px 15px rgba(0,0,0,.05)}.review-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}.reviewer-info h4{margin:0;color:#0b1c4f;font-size:16px}.review-date{font-size:12px;color:#828386}.review-text{color:#555;font-size:14px;line-height:1.5;margin:0}.loader,.no-reviews{text-align:center;color:#828386;padding:40px;background:#f6f8fb;border-radius:20px}@media (max-width:768px){.feedback-wrapper{grid-template-columns:1fr}.page-header h1{font-size:36px}}
</style>