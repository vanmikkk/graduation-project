<template>
  <div class="chart-widget">
    <!-- Мини-превью (Sparkline) -->
    <div class="chart-preview" @click="openDetailModal">
      <div class="preview-header">
        <span class="title">Успеваемость</span>
        <span class="avg-score" :class="{ 'no-data': averageScore === 0 }">
          {{ averageScore > 0 ? averageScore.toFixed(2) : '–' }}
        </span>
      </div>
      
      <div class="canvas-container" v-if="hasGrades">
        <Line :data="chartData" :options="previewOptions" />
      </div>
      
      <div v-else class="no-data-placeholder">
        <p>Нет оценок</p>
      </div>
      
      <div class="click-hint" v-if="hasGrades">
        Подробнее →
      </div>
    </div>

    <!-- Модальное окно с подробным графиком -->
     <Teleport to="body">
        <div v-if="isDetailOpen" class="modal-overlay" @click.self="closeDetailModal">
          <div class="modal-large">
            <div class="modal-head">
              <h3>Динамика успеваемости</h3>
              <button @click="closeDetailModal" class="close-x">✕</button>
            </div>
            
            <div class="modal-body-chart">
              <div class="stats-row">
                <div class="stat-item">
                  <span class="stat-label">Средний балл</span>
                  <span class="stat-value big">{{ averageScore.toFixed(2) }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Оцененных работ</span>
                  <span class="stat-value big">{{ gradedCount }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Лучшая оценка</span>
                  <span class="stat-value big best">{{ maxScore }}</span>
                </div>
              </div>
              
              <div class="big-chart-container">
                <Line :data="chartData" :options="detailOptions" />
              </div>

              <!-- Таблица деталей -->
              <div class="details-table-wrapper">
                <table class="details-table">
                  <thead>
                    <tr>
                      <th>Задание</th>
                      <th>Дата сдачи</th>
                      <th>Оценка</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in gradedSubmissions" :key="index">
                      <td>{{ item.assignment_name || 'Задание #' + item.assignment_id }}</td>
                      <td>{{ item.formatted_date }}</td>
                      <td>
                        <span class="grade-badge" :class="getGradeClass(item.score)">
                          {{ item.score }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
    </Teleport>
  </div>
</template>

<script>
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

// Регистрируем компоненты Chart.js
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

export default {
  name: 'GradeChart',
  components: { Line },
  props: {
    submissions: {
      type: Array,
      required: true
    },
    assignments: {
      type: Array,
      required: true // Нужно, чтобы подставить названия заданий
    }
  },
  data() {
    return {
      isDetailOpen: false
    };
  },
  computed: {
    // Фильтруем только работы с оценкой
    gradedSubmissions() {
      const graded = this.submissions
        .filter(s => s.score !== null && s.score !== '' && s.score !== undefined)
        .map(s => {
          // Находим название задания
          const assignment = this.assignments.find(a => a.id === s.assignment_id);
          const dateObj = new Date(s.created_at);
          const formatted = dateObj.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
          
          return {
            ...s,
            assignment_name: assignment ? assignment.text : 'Задание',
            formatted_date: formatted
          };
        });
      
      // Сортируем по дате сдачи (возрастание для графика слева направо)
      return graded.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    },
    
    hasGrades() {
      return this.gradedSubmissions.length > 0;
    },
    
    averageScore() {
      if (!this.hasGrades) return 0;
      const sum = this.gradedSubmissions.reduce((acc, curr) => acc + Number(curr.score), 0);
      return sum / this.gradedSubmissions.length;
    },
    
    gradedCount() {
      return this.gradedSubmissions.length;
    },
    
    maxScore() {
      if (!this.hasGrades) return 0;
      return Math.max(...this.gradedSubmissions.map(s => Number(s.score)));
    },

    // Данные для графика
    chartData() {
      const labels = this.gradedSubmissions.map(s => s.formatted_date);
      const data = this.gradedSubmissions.map(s => Number(s.score));

      return {
        labels: labels,
        datasets: [
          {
            label: 'Оценки',
            data: data,
            borderColor: '#0966F6',
            backgroundColor: 'rgba(9, 102, 246, 0.1)',
            borderWidth: 2,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#0966F6',
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.3 // Плавность линии
          }
        ]
      };
    },
    
    // Опции для мини-графика (упрощенные)
    previewOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: {
          x: { display: false }, // Скрываем оси для миниатюры
          y: { display: false, min: 0, max: 6 }
        },
        elements: { point: { radius: 0 } }, // Скрываем точки для чистоты
        animation: false
      };
    },
    
    // Опции для большого графика (подробные)
    detailOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => `Оценка: ${ctx.raw}`
            }
          }
        },
        scales: {
          x: { grid: { display: false } },
          y: { 
            beginAtZero: false, 
            min: 0, 
            max: 6, 
            ticks: { stepSize: 1 },
            grid: { color: '#F1F5F9' }
          }
        }
      };
    }
  },
  methods: {
    openDetailModal() {
      if (this.hasGrades) {
        this.isDetailOpen = true;
        document.body.style.overflow = 'hidden'; 
        document.body.style.pointerEvents = 'none';
      }
    },
    closeDetailModal() {
      this.isDetailOpen = false;
      document.body.style.overflow = ''; 
        document.body.style.pointerEvents = ''; 
    },
    getGradeClass(score) {
      if (score >= 5) return 'grade-excellent';
      if (score >= 4) return 'grade-good';
      if (score >= 3) return 'grade-satisfactory';
      return 'grade-bad';
    }
  }
};
</script>

<style scoped>
/* Виджет в сайдбаре */
.chart-widget {
  margin-top: 10px;
  z-index: 1000;
}
.chart-preview {
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}
.chart-preview:hover {
  border-color: #0966F6;
  box-shadow: 0 4px 12px rgba(9, 102, 246, 0.1);
  transform: translateY(-2px);
}
.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.title {
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
  text-transform: uppercase;
}
.avg-score {
  font-size: 18px;
  font-weight: 800;
  color: #0966F6;
  background: #EFF6FF;
  padding: 2px 8px;
  border-radius: 6px;
}
.avg-score.no-data {
  color: #94A3B8;
  background: #F1F5F9;
}
.canvas-container {
  height: 100px; /* Высота мини-графика */
  width: 100%;
}
.no-data-placeholder {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94A3B8;
  font-size: 13px;
  font-style: italic;
}
.click-hint {
  text-align: center;
  font-size: 11px;
  color: #0966F6;
  font-weight: 600;
  margin-top: 8px;
  opacity: 0.8;
}

/* Модальное окно */
.modal-overlay {
  position: fixed; 
  top: 0; 
  left: 0; 
  width: 100%; 
  height: 100%;
  background: rgba(0,0,0,0.6); /* Убедись, что фон достаточно темный */
  display: flex; 
  align-items: center; 
  justify-content: center;
  
  /* ВАЖНО: Ставим очень высокий индекс, но меньше чем у системных алертов */
  pointer-events: auto;
  z-index: 9999999 !important;  
  
  backdrop-filter: blur(5px); /* Размытие фона поможет скрыть лишнее */
}

/* Само окно */
.modal-large {
  background: #fff;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  border-radius: 20px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: slideIn 0.3s ease;
  
  /* Окно тоже должно быть выше всего */
  z-index: 9999999 !important; 
  position: relative;
  pointer-events: auto;
}
@keyframes slideIn {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.modal-head {
  padding: 20px 25px;
  border-bottom: 1px solid #F1F5F9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-head h3 { margin: 0; font-size: 20px; color: #0B1C4F; }
.close-x {
  background: none; border: none; font-size: 24px; cursor: pointer; color: #94A3B8;
}
.modal-body-chart {
  padding: 25px;
  overflow-y: auto;
}
.stats-row {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  justify-content: space-around;
  flex-wrap: wrap;
}
.stat-item {
  text-align: center;
  background: #F8FAFC;
  padding: 15px 25px;
  border-radius: 12px;
  min-width: 120px;
}
.stat-label {
  display: block;
  font-size: 12px;
  color: #64748B;
  text-transform: uppercase;
  font-weight: 700;
  margin-bottom: 5px;
}
.stat-value.big {
  font-size: 28px;
  font-weight: 800;
  color: #0B1C4F;
}
.stat-value.best { color: #16A34A; }

.big-chart-container {
  height: 300px;
  width: 100%;
  margin-bottom: 30px;
}

/* Таблица */
.details-table-wrapper {
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  overflow: hidden;
}
.details-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
.details-table th {
  background: #F8FAFC;
  text-align: left;
  padding: 12px 15px;
  font-weight: 600;
  color: #475569;
  border-bottom: 1px solid #E2E8F0;
}
.details-table td {
  padding: 12px 15px;
  border-bottom: 1px solid #F1F5F9;
  color: #334155;
}
.details-table tr:last-child td { border-bottom: none; }

.grade-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-weight: 700;
  font-size: 13px;
  min-width: 20px;
  text-align: center;
}
.grade-excellent { background: #DCFCE7; color: #16A34A; }
.grade-good { background: #DBEAFE; color: #2563EB; }
.grade-satisfactory { background: #FEF3C7; color: #D97706; }
.grade-bad { background: #FEE2E2; color: #DC2626; }

@media (max-width: 600px) {
  .stats-row { flex-direction: column; gap: 10px; }
  .stat-item { width: 100%; box-sizing: border-box; }
  .big-chart-container { height: 200px; }
  .details-table thead { display: none; }
  .details-table tr { display: flex; flex-direction: column; border-bottom: 2px solid #F1F5F9; padding: 10px; }
  .details-table td { border: none; padding: 5px 0; }
  .details-table td::before { content: attr(data-label); font-weight: bold; display: block; color: #64748B; font-size: 11px; text-transform: uppercase; }
}
</style>