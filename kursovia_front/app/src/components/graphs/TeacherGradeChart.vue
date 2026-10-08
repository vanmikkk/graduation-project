<template>
  <div class="teacher-chart-widget">
    <!-- Мини-превью в сайдбаре -->
    <div class="chart-preview" @click="openDetailModal">
      <div class="preview-header">
        <span class="title">Распределение оценок</span>
        <span class="avg-score" :class="{ 'no-data': gradedCount === 0 }">
          {{ averageScore > 0 ? averageScore.toFixed(2) : '–' }}
        </span>
      </div>
      
      <div class="canvas-container" v-if="gradedCount > 0">
        <Bar :data="chartData" :options="previewOptions" />
      </div>
      
      <div v-else class="no-data-placeholder">
        <p>Нет оцененных работ</p>
      </div>
      
      <div class="click-hint" v-if="gradedCount > 0">
        Подробнее →
      </div>
    </div>

    <!-- Модальное окно с подробностями -->
    <Teleport to="body">
      <div v-if="isDetailOpen" class="modal-overlay-global" @click.self="closeDetailModal">
        <div class="modal-large">
          <div class="modal-head">
            <h3>Статистика по заданию</h3>
            <button @click="closeDetailModal" class="close-x">✕</button>
          </div>
          
          <div class="modal-body-chart">
            <!-- Карточки статистики -->
            <div class="stats-row">
              <div class="stat-item">
                <span class="stat-label">Средний балл</span>
                <span class="stat-value big">{{ averageScore.toFixed(2) }}</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">Проверено работ</span>
                <span class="stat-value big">{{ gradedCount }}</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">Всего сдали</span>
                <span class="stat-value big">{{ submissions.length }}</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">Ждут проверки</span>
                <span class="stat-value big warning">{{ submissions.length - gradedCount }}</span>
              </div>
            </div>
            
            <!-- Большой график -->
            <div class="big-chart-container">
              <Bar :data="chartData" :options="detailOptions" />
            </div>

            <!-- Таблица деталей -->
            <div class="details-table-wrapper">
              <h4 class="table-title">Детализация по оценкам</h4>
              <table class="details-table">
                <thead>
                  <tr>
                    <th>Оценка</th>
                    <th>Количество студентов</th>
                    <th>Процент от группы</th>
                    <th>Студенты</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="count in gradeCounts" :key="count.grade">
                    <td>
                      <span class="grade-badge" :class="getGradeClass(count.grade)">
                        {{ count.grade }}
                      </span>
                    </td>
                    <td>{{ count.count }} чел.</td>
                    <td>
                      <div class="progress-bar-bg">
                        <div class="progress-bar-fill" :style="{ width: count.percentage + '%' }"></div>
                      </div>
                      <span class="percentage-text">{{ count.percentage }}%</span>
                    </td>
                    <td class="students-list">
                      {{ count.names.join(', ') }}
                    </td>
                  </tr>
                  <tr v-if="gradedCount === 0">
                    <td colspan="4" style="text-align:center; color:#94A3B8;">
                      Пока нет ни одной оценки
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
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export default {
  name: 'TeacherGradeChart',
  components: { Bar },
  props: {
    submissions: {
      type: Array,
      required: true
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
      return this.submissions.filter(s => 
        s.score !== null && s.score !== undefined && s.score !== ''
      );
    },
    
    gradedCount() {
      return this.gradedSubmissions.length;
    },
    
    averageScore() {
      if (!this.gradedCount) return 0;
      const sum = this.gradedSubmissions.reduce((acc, curr) => acc + Number(curr.score), 0);
      return sum / this.gradedCount;
    },

    // Группируем данные для графика и таблицы: { grade: 5, count: 3, names: [...] }
    gradeCounts() {
      const counts = { 2: 0, 3: 0, 4: 0, 5: 0 };
      const names = { 2: [], 3: [], 4: [], 5: [] };

      this.gradedSubmissions.forEach(s => {
        const score = Number(s.score);
        if (counts[score] !== undefined) {
          counts[score]++;
          names[score].push(s.FIO || 'Студент');
        }
      });

      return [2, 3, 4, 5].map(grade => ({
        grade,
        count: counts[grade],
        names: names[grade],
        percentage: this.gradedCount ? Math.round((counts[grade] / this.gradedCount) * 100) : 0
      })).reverse(); // Чтобы 5 было слева (или убери reverse, если нужно как обычно)
    },

    // Данные для Chart.js
    chartData() {
      // Берем данные из gradeCounts, но в правильном порядке для графика (2, 3, 4, 5)
      const labels = ['2', '3', '4', '5'];
      const data = [
        this.gradeCounts.find(g => g.grade === 2)?.count || 0,
        this.gradeCounts.find(g => g.grade === 3)?.count || 0,
        this.gradeCounts.find(g => g.grade === 4)?.count || 0,
        this.gradeCounts.find(g => g.grade === 5)?.count || 0,
      ];

      const colors = [
        'rgba(220, 38, 38, 0.7)', // Красный для 2
        'rgba(234, 179, 8, 0.7)', // Желтый для 3
        'rgba(59, 130, 246, 0.7)', // Синий для 4
        'rgba(34, 197, 94, 0.7)'   // Зеленый для 5
      ];
      
      const borderColors = [
        'rgba(220, 38, 38, 1)',
        'rgba(234, 179, 8, 1)',
        'rgba(59, 130, 246, 1)',
        'rgba(34, 197, 94, 1)'
      ];

      return {
        labels: labels,
        datasets: [{
          label: 'Количество студентов',
          data: data,
          backgroundColor: colors,
          borderColor: borderColors,
          borderWidth: 1,
          borderRadius: 6,
          barPercentage: 0.6
        }]
      };
    },
    
    previewOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: {
          x: { display: false },
          y: { display: false, beginAtZero: true }
        },
        animation: false
      };
    },
    
    detailOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => `${ctx.raw} студент(ов)`
            }
          }
        },
        scales: {
          x: { grid: { display: false } },
          y: { 
            beginAtZero: true, 
            ticks: { stepSize: 1 },
            grid: { color: '#F1F5F9' }
          }
        }
      };
    }
  },
  methods: {
    openDetailModal() {
      if (this.gradedCount > 0) {
        this.isDetailOpen = true;
        document.body.style.overflow = 'hidden';
      }
    },
    closeDetailModal() {
      this.isDetailOpen = false;
      document.body.style.overflow = '';
    },
    getGradeClass(score) {
      if (score == 5) return 'grade-excellent';
      if (score == 4) return 'grade-good';
      if (score == 3) return 'grade-satisfactory';
      return 'grade-bad';
    }
  },
  beforeUnmount() {
    document.body.style.overflow = '';
  }
};
</script>

<style scoped>
/* Виджет */
.teacher-chart-widget { margin-top: 10px; }
.chart-preview {
  background: #fff; border: 1px solid #E2E8F0; border-radius: 12px;
  padding: 15px; cursor: pointer; transition: all 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}
.chart-preview:hover {
  border-color: #0966F6; box-shadow: 0 4px 12px rgba(9, 102, 246, 0.1);
  transform: translateY(-2px);
}
.preview-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;
}
.title { font-size: 13px; font-weight: 600; color: #64748B; text-transform: uppercase; }
.avg-score {
  font-size: 18px; font-weight: 800; color: #0966F6;
  background: #EFF6FF; padding: 2px 8px; border-radius: 6px;
}
.avg-score.no-data { color: #94A3B8; background: #F1F5F9; }
.canvas-container { height: 80px; width: 100%; }
.no-data-placeholder {
  height: 80px; display: flex; align-items: center; justify-content: center;
  color: #94A3B8; font-size: 13px; font-style: italic; text-align: center;
}
.click-hint {
  text-align: center; font-size: 11px; color: #0966F6; font-weight: 600; margin-top: 8px; opacity: 0.8;
}

/* Модалка (Global через Teleport) */
.modal-overlay-global {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(5px);
}
.modal-large {
  background: #fff; width: 90%; max-width: 900px; max-height: 90vh;
  border-radius: 20px; overflow: hidden; display: flex; flex-direction: column;
  animation: slideIn 0.3s ease;
}
@keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-head {
  padding: 20px 25px; border-bottom: 1px solid #F1F5F9;
  display: flex; justify-content: space-between; align-items: center;
}
.modal-head h3 { margin: 0; font-size: 20px; color: #0B1C4F; }
.close-x { background: none; border: none; font-size: 24px; cursor: pointer; color: #94A3B8; }
.close-x:hover { color: #334155; }
.modal-body-chart { padding: 25px; overflow-y: auto; }

/* Статистика */
.stats-row {
  display: flex; gap: 15px; margin-bottom: 30px; justify-content: space-between; flex-wrap: wrap;
}
.stat-item {
  text-align: center; background: #F8FAFC; padding: 15px;
  border-radius: 12px; flex: 1; min-width: 140px;
}
.stat-label { display: block; font-size: 11px; color: #64748B; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; }
.stat-value.big { font-size: 24px; font-weight: 800; color: #0B1C4F; }
.stat-value.big.warning { color: #F59E0B; }

/* График */
.big-chart-container { height: 300px; width: 100%; margin-bottom: 30px; }

/* Таблица */
.details-table-wrapper { border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; }
.table-title { margin: 0; padding: 15px; background: #F8FAFC; font-size: 14px; color: #475569; border-bottom: 1px solid #E2E8F0; }
.details-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.details-table th { text-align: left; padding: 12px 15px; font-weight: 600; color: #475569; background: #fff; border-bottom: 2px solid #F1F5F9; }
.details-table td { padding: 12px 15px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
.details-table tr:last-child td { border-bottom: none; }

.grade-badge {
  display: inline-block; padding: 4px 10px; border-radius: 20px;
  font-weight: 700; font-size: 13px; min-width: 20px; text-align: center;
}
.grade-excellent { background: #DCFCE7; color: #16A34A; }
.grade-good { background: #DBEAFE; color: #2563EB; }
.grade-satisfactory { background: #FEF3C7; color: #D97706; }
.grade-bad { background: #FEE2E2; color: #DC2626; }

/* Прогресс бар */
.progress-bar-bg {
  display: inline-block; width: 100px; height: 6px; background: #E2E8F0;
  border-radius: 3px; overflow: hidden; vertical-align: middle; margin-right: 8px;
}
.progress-bar-fill { height: 100%; background: #0966F6; border-radius: 3px; }
.percentage-text { font-size: 12px; color: #64748B; font-weight: 600; }
.students-list { font-size: 12px; color: #64748B; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

@media (max-width: 768px) {
  .stats-row { flex-direction: column; }
  .details-table thead { display: none; }
  .details-table tr { display: flex; flex-direction: column; border: 1px solid #E2E8F0; margin-bottom: 10px; border-radius: 8px; padding: 10px; }
  .details-table td { border: none; padding: 5px 0; width: 100%; }
  .students-list { max-width: 100%; white-space: normal; }
}
</style>