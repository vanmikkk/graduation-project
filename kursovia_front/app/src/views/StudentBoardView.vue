<template>
  <main class="board-page">
    <div class="container">
      <!-- Шапка -->
      <div class="board-header">
        <div>
          <h1>Мои задачи</h1>
          <p class="subtitle">Обзор всех заданий из ваших курсов</p>
        </div>
        <button @click="loadBoard" class="refresh-btn" :disabled="loading">Обновить</button>
      </div>

      <!-- Лоадер -->
      <div v-if="loading && !board" class="loader-container">
        <div class="spinner"></div>
        <p>Загрузка задач...</p>
      </div>

      <!-- Доска -->
      <div v-else class="kanban-board">
        <div class="column column-todo">
          <div class="column-header">
              <h3>Нужно сделать</h3>
              <div class="badges-wrapper">
                <span class="count-badge">{{ filteredTodo.length }}</span>
                <span v-if="overdueCount > 0" class="overdue-badge" title="Просроченные задания">
                    {{ overdueCount }}
                </span>
              </div>
          </div>
          <div class="column-body">
              <TaskCard 
                  v-for="task in filteredTodo" 
                  :key="task.id"
                  v-bind="task"
                  :score="task.score"
                  status="todo"
                  @preview="openPreview"
              />
              <!-- Показываем "Нет задач", если отфильтрованный список пуст -->
              <div v-if="filteredTodo.length === 0" class="empty-column">Нет актуальных задач</div>
          </div>
        </div>

        <!-- Колонка 2 -->
        <div class="column column-pending">
            <div class="column-header">
                <h3>На проверке</h3>
                <span class="count-badge yellow">{{ board.pending.length }}</span>
            </div>
            <div class="column-body">
                <TaskCard 
                    v-for="task in board.pending" 
                    :key="task.id"
                    v-bind="task"
                    :score="task.score"
                    status="pending"
                    @preview="openPreview"
                />
                <div v-if="board.pending.length === 0" class="empty-column">Пусто</div>
            </div>
        </div>

        <!-- Колонка 3 -->
        <div class="column column-done">
            <div class="column-header">
                <h3>Выполнено</h3>
                <span class="count-badge green">{{ board.done.length }}</span>
            </div>
            <div class="column-body">
                <TaskCard 
                    v-for="task in board.done" 
                    :key="task.id"
                    v-bind="task"
                    :score="task.score"
                    status="done"
                    @preview="openPreview"
                />
                <div v-if="board.done.length === 0" class="empty-column">Пока ничего</div>
            </div>
        </div>
       </div>
    </div>
    <!-- ПОДКЛЮЧАЕМ МОДАЛЬНОЕ ОКНО ЗДЕСЬ -->
    <TaskPreviewModal 
      :is-open="isPreviewOpen" 
      :task="selectedTask" 
      @close="closePreview" 
    />
  </main>
</template>

<script>
import TaskCard from '@/components/board/TaskCard.vue';
import TaskPreviewModal from '@/components/board/TaskPreviewModal.vue'; // Импортируем модалку

export default {
  name: 'StudentBoardView',
  components: { TaskCard, TaskPreviewModal },
  data() {
    return {
      loading: false,
      board: { todo: [], pending: [], done: [] },
      isPreviewOpen: false,
      selectedTask: {} // Здесь будем хранить данные выбранной задачи
    };
  },
  async mounted() {
    await this.loadBoard();
  },
  computed: {
    filteredTodo() {
      if (!this.board.todo) return [];
      return this.board.todo.filter(task => !task.is_overdue);
    },
    overdueCount() {
      if (!this.board.todo) return 0;
      return this.board.todo.filter(task => task.is_overdue).length;
    }
  },
  methods: {
    async loadBoard() {
      this.loading = true;
      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      headers.append("Content-Type", "application/json");

      try {
        const response = await fetch(`${process.env.VUE_APP_API_BASE_URL}/student/board`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({}) 
        });
        
        if (response.status === 401) {
          localStorage.removeItem("token"); 
          alert("Неверный токен авторизации, авторизуйтесь заново");
          this.$router.push("/login");
          return;                            
        }
        
        if (!response.ok) throw new Error("Ошибка");
        
        const data = await response.json();
        
        // ✅ ИСПРАВЛЕНИЕ: Нормализуем даты перед присвоением
        // Получаем текущую дату БЕЗ времени (начало сегодняшнего дня)
        const today = new Date();
        today.setHours(0, 0, 0, 0); 

        // Проходим по всем задачам во всех колонках и пересчитываем is_overdue
        ['todo', 'pending', 'done'].forEach(status => {
          if (data[status]) {
            data[status].forEach(task => {
              if (task.deadline) {
                const deadlineDate = new Date(task.deadline);
                deadlineDate.setHours(0, 0, 0, 0);
                
                // Задача просрочена ТОЛЬКО если дедлайн строго меньше сегодняшней даты
                task.is_overdue = deadlineDate < today;
              } else {
                task.is_overdue = false;
              }
            });
          }
        });

        this.board = data;
        
      } catch (error) {
        console.error(error);
        alert("Не удалось загрузить задачи");
      } finally {
        this.loading = false;
      }
    },
    
    // Методы для управления модалкой
    openPreview(taskData) {
      this.selectedTask = taskData;
      this.isPreviewOpen = true;
    },
    closePreview() {
      this.isPreviewOpen = false;
      this.selectedTask = {};
    }
  }
};
</script>

<style scoped>
.board-page {
  font-family: 'Inter', sans-serif;
  background: #F8FAFC;
  min-height: 100vh;
  padding: 40px 0;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
  width: 100%; /* Гарантируем, что контейнер не шире экрана */
  box-sizing: border-box;
}

/* Шапка */
.board-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  gap: 20px; /* Чтобы шапка не ломалась на узких экранах */
}

.board-header h1 {
  font-size: 32px;
  color: #0B1C4F;
  margin: 0;
  white-space: nowrap; /* Заголовок не переносится */
}

.subtitle {
  color: #64748B;
  margin: 5px 0 0 0;
}

.refresh-btn {
  background: #fff;
  border: 1px solid #CBD5E1;
  color: #475569;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  white-space: nowrap;
  flex-shrink: 0; /* Кнопка не сжимается */
}

.refresh-btn:hover:not(:disabled) {
  background: #F1F5F9;
  border-color: #94A3B8;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Лоадер */
.loader-container {
  text-align: center;
  padding: 100px;
  color: #64748B;
}
.spinner {
  width: 40px; height: 40px;
  border: 4px solid #E2E8F0;
  border-top-color: #0966F6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Доска (Grid) */
.kanban-board {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  align-items: start;
  width: 100%;
}

/* Колонки */
.column {
  background: #F1F5F9;
  border-radius: 16px;
  padding: 15px;
  min-height: 500px;
  display: flex;
  flex-direction: column;
  
  /* ✅ КЛЮЧЕВОЕ ИСПРАВЛЕНИЕ ДЛЯ АДАПТИВНОСТИ */
  width: 100%;
  min-width: 0; /* Разрешает колонке сжиматься, если контент длинный */
  overflow: hidden; /* Обрезает всё, что вылезает за границы колонки */
  box-sizing: border-box;
}

.column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding: 0 5px;
  flex-shrink: 0; /* Заголовок всегда фиксированной высоты */
}

.column-header h3 {
  font-size: 16px;
  color: #334155;
  margin: 0;
  font-weight: 700;
}

.count-badge {
  background: #fff;
  color: #64748B;
  font-size: 12px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
  border: 1px solid #E2E8F0;
}

.count-badge.yellow { background: #FEF3C7; color: #D97706; border-color: #FCD34D; }
.count-badge.green { background: #DCFCE7; color: #16A34A; border-color: #86EFAC; }

.column-body {
  flex: 1;
  overflow-y: auto;
  /* Адаптивная высота: учитывает отступы и шапку страницы */
  max-height: calc(100vh - 300px); 
  padding-right: 5px;
  margin-right: -5px; /* Компенсация ширины скроллбара, чтобы контент не дергался */
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Стилизация скроллбара */
.column-body::-webkit-scrollbar { width: 6px; }
.column-body::-webkit-scrollbar-track { background: transparent; }
.column-body::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }
.column-body::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

.empty-column {
  text-align: center;
  color: #94A3B8;
  font-size: 14px;
  padding: 40px 0;
  font-style: italic;
}

.badges-wrapper {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-shrink: 0;
}

.overdue-badge {
    background: #FEE2E2;
    color: #DC2626;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 10px;
    border: 1px solid #FCA5A5;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
    70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}

/* Адаптивность для планшетов и мобильных */
@media (max-width: 1024px) {
  .kanban-board {
    grid-template-columns: 1fr; /* Одна колонка на всю ширину */
    gap: 15px;
  }
  
  .column {
    min-height: auto;
    max-height: 600px; /* Ограничиваем высоту на мобильных */
  }
  
  .board-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .refresh-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>