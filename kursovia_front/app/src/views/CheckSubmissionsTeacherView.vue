<template>
  <main class="review-page">
    <div class="container">
      <div class="main-layout">
        
        <!-- ЛЕВАЯ КОЛОНКА (24%) -->
        <aside class="sidebar">
          <div class="sidebar-content">
            <h1 class="page-title">Проверка работ</h1>
            
            <div class="course-card-mini" v-if="response.name">
              <div class="mini-cover">
                <img :src="courseImages[response.img_id] || courseImages[1]" alt="Обложка">
              </div>
              <h3 class="course-name">{{ response.name }}</h3>
            </div>

            <div class="sidebar-actions">
              <router-link :to="'/checkCourseMain/' + response.id" class="btn-sidebar">
                ← Назад к заданиям
              </router-link>
              <router-link to="/courses" class="btn-sidebar-outline">
                К списку курсов
              </router-link>
              <button @click="exportToExcel" class="btn-export-excel">
                Экспорт в Excel
              </button>
            </div>

            <TeacherGradeChart :submissions="submissions" />

          </div>
        </aside>

        <!-- ПРАВАЯ КОЛОНКА (74%) -->
        <section class="content-area">
          
          <!-- Блок описания задания -->
          <div class="assignment-header-card">
            <div class="header-top">
              <h2 class="assign-title">{{ responseAssignment.text }}</h2>
              <div class="deadline-badge" :class="{ overdue: responseAssignment.isOverdue }">
                {{ responseAssignment.isOverdue ? 'Просрочено' : 'Активно' }}
              </div>
            </div>
            <p class="assign-desc">{{ responseAssignment.description }}</p>
            <div class="assign-meta-row">
              <span class="meta-label">Срок сдачи:</span>
              <span class="meta-value" :style="{ color: responseAssignment.isOverdue ? '#DC2626' : '#16A34A' }">
                {{ responseAssignment.formattedTime }}
              </span>
              <a :href="'http://classman24.ru/storage/' + responseAssignment.file" target="_blank" class="btn-file-small">
                Скачать файл задания
              </a>
              <button @click="OpenModalChange(responseAssignment.id)" class="btn-edit-small">
                Редактировать задание
              </button>
            </div>
          </div>

          <!-- Заголовок списка работ -->
          <div class="submissions-header">
            <h3>Работы студентов</h3>
            <span class="count-badge">{{ submissions.length }}</span>

            <div class="filter-toggle">
              <button 
                @click="showGraded = true" 
                :class="['toggle-btn', { active: showGraded }]"
              >
                Все работы
              </button>
              <button 
                @click="showGraded = false" 
                :class="['toggle-btn', { active: !showGraded }]"
              >
                Требуют проверки
              </button>
            </div>
          </div>
          
          <!-- Пустое состояние -->
          <div v-if="filteredSubmissions.length === 0" class="empty-state">
            <p v-if="showGraded">
              Студенты еще не сдали работы по этому заданию.
            </p>
            <p v-else>
              Все работы проверены!
            </p>
          </div>

          <!-- Список работ -->
          <div v-for="submission in filteredSubmissions" :key="submission.id" class="submission-card">
            <div class="sub-top">
              <div class="student-info">
                <h4 class="student-name">{{ submission.FIO }}</h4>
                <div class="sub-status" :style="{ color: submission.isOverdue ? '#DC2626' : '#16A34A' }">
                  {{ submission.isOverdue ? 'Сдано с опозданием' : 'Сдано в срок' }}
                  <span class="sub-date">{{ submission.formattedTime }}</span>
                </div>
              </div>
              <a :href="'http://classman24.ru/storage/' + submission.file" target="_blank" class="btn-download">
                Скачать работу
              </a>
            </div>

            <div class="sub-body">
              <div class="user-comment-block">
                <label>Комментарий студента:</label>
                <p class="comment-text">{{ submission.user_comment || 'Нет комментария' }}</p>
              </div>

              <div class="teacher-review-block">
                <div class="grade-input-group">
                  <label for="score-{{ submission.id }}">Оценка:</label>
                  <input 
                    :id="`score-${submission.id}`"
                    type="number" 
                    step="1"
                    :value="submission.score"
                    @input="validateScore(submission, $event)"
                    @blur="normalizeScore(submission)"
                    class="score-input"
                    placeholder="-"
                  />
                </div>

                <div class="comment-input-group">
                  <label :for="`teacher-comment-${submission.id}`">Ваш комментарий:</label>
                  <textarea 
                    :id="`teacher-comment-${submission.id}`"
                    class="teacher-comment-input" 
                    placeholder="Напишите комментарий..." 
                    @change="changeComment(submission.id)" 
                    v-model="submission.teacher_comment"
                    rows="3"
                  ></textarea>
                </div>
              </div>
            </div>
            
            <div class="sub-footer" v-if="submission.score || submission.teacher_comment">
              <span class="save-hint">Изменения сохраняются автоматически</span>
            </div>
          </div>

        </section>
      </div>
    </div>

    <!-- Модальное окно: ИЗМЕНИТЬ ЗАДАНИЕ -->
    <div v-if="isOpen" class="modal-overlay" @click.self="CloseModalChange">
      <div class="modal-window">
        <div class="modal-head">
          <h3>Редактировать задание</h3>
          <button @click="CloseModalChange" class="close-x">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Название</label>
            <input type="text" v-model="form.name" placeholder="Введите название" class="form-input" />
          </div>
          <div class="form-group">
            <label>Срок сдачи</label>
            <input type="date" v-model="form.time" class="form-input" :min="todayDate"/>
          </div>
          <div class="form-group">
            <label>Описание</label>
            <textarea v-model="form.description" placeholder="Подробности задания..." class="form-textarea"></textarea>
          </div>
          <div class="form-group">
            <label>Файл задания</label>
            <!-- Добавлен класс upload-error для подсветки при ошибке -->
            <div class="file-upload-box" @click="$refs.fileInput.click()" :class="{ 'upload-error': fileError }">
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileSelect" 
                hidden 
                accept=".pdf,.doc,.docx,.xls,.xlsx,.xlsm,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.svg" 
              />
              <div class="upload-icon">📁</div>
              <p class="file-hint">{{ fileName || 'Нажмите, чтобы выбрать новый файл' }}</p>
              <p v-if="fileName && !isFileChanged" class="current-file">Текущий: {{ currentFileName }}</p>
            </div>
            <!-- Вывод текста ошибки -->
            <p v-if="fileError" class="error-text">{{ fileError }}</p>
          </div>
        </div>
        <div class="modal-foot">
          <button @click="CloseModalChange" class="btn-cancel">Отмена</button>
          <button @click="submitAnswer" :disabled="isLoading" class="btn-confirm">
            {{ isLoading ? 'Сохранение...' : 'Сохранить' }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import TeacherGradeChart from '@/components/graphs/TeacherGradeChart.vue';
import * as XLSX from 'xlsx';

export default {
  name: 'TeacherSubmissionView',
  components: {
    TeacherGradeChart,
  },
  data() {
    return {
      isOpen: false,
      isLoading: false,
      fileName: '',
      currentFileName: '',
      selectedFile: null,
      isFileChanged: false, 
      fileError: '',
      form: { name: "", time: "", description: "" },
      user_id: "",
      response: {},
      responseAssignment: {},
      submissions: [],
      courseImages: {
        1: require('@/assets/course_img1.png'), 
        2: require('@/assets/course_img2.png'),
        3: require('@/assets/course_img3.png'),
        4: require('@/assets/course_img4.png'),
        5: require('@/assets/course_img5.png'),
        6: require('@/assets/course_img6.png'),
        7: require('@/assets/course_img7.png'),
        8: require('@/assets/course_img8.png'),
        9: require('@/assets/course_img9.png'),
        10: require('@/assets/course_img10.png'),
      },
      courseId: this.$route.params.id,
      assignmentId: this.$route.params.assignment_id,
      message: "",
      showGraded: true,
    };
  },
  async mounted() {
    await this.loadData();
  },
  computed: {
    todayDate() {
      const date = new Date();
      const offset = date.getTimezoneOffset() * 60000;
      const localISOTime = (new Date(date - offset)).toISOString().slice(0, 10);
      return localISOTime;
    },
    filteredSubmissions() {
    if (this.showGraded) {
      return this.submissions;
    } else {
      return this.submissions.filter(s => !s.score);
    }
  }
  },
  methods: {
    exportToExcel() {
      if (this.submissions.length === 0) {
        alert("Нет данных для экспорта");
        return;
      }

      const dataForExport = this.submissions.map(sub => ({
        "ФИО Студента": sub.FIO || "Не указано",
        "Статус сдачи": sub.isOverdue ? "С опозданием" : "В срок",
        "Дата сдачи": sub.formattedTime || "-",
        "Оценка": sub.score !== null && sub.score !== undefined ? sub.score : "Не оценено",
        "Комментарий студента": sub.user_comment || "-",
        "Комментарий преподавателя": sub.teacher_comment || "-"
      }));

      const ws = XLSX.utils.json_to_sheet(dataForExport);

      const wscols = [
        { wch: 25 }, // ФИО
        { wch: 15 }, // Статус
        { wch: 15 }, // Дата
        { wch: 10 }, // Оценка
        { wch: 30 }, // Комментарий студента
        { wch: 30 }  // Комментарий преподa
      ];
      ws['!cols'] = wscols;

      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Успеваемость");

      const courseName = this.response.name || "Курс";
      const assignmentName = this.responseAssignment.text || "Задание";
      const fileName = `${courseName}_${assignmentName}_Отчет.xlsx`;

      XLSX.writeFile(wb, fileName);
    },
    async loadData() {
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/course`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if (courseReq.status === 401) {
          localStorage.removeItem("token"); 
          alert("Неверный токен авторизации, авторизуйтесь заново")
          this.$router.push("/login");
          return;                            
        }
        if (!courseReq.ok) throw new Error("Нет доступа");
        this.response = await courseReq.json();

        const userReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/users`, { method: "POST", headers });
        if (!userReq.ok) throw new Error("Ошибка");
        this.user_id = await userReq.json();
        if (this.user_id != this.response.user_id) {
          window.location.href = `/`;
          return;
        }

        const assignReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/assignment/get/${this.assignmentId}`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if (!assignReq.ok) throw new Error("Ошибка");
        const assignData = await assignReq.json();
        
        // Обработка даты задания
        const now = new Date(); now.setHours(0,0,0,0);
        const d = assignData.time ? new Date(assignData.time) : null;
        if(d) d.setHours(0,0,0,0);
        const isOverdue = d && d < now;
        
        let formattedTime = 'Нет срока';
        if (assignData.time) {
          const months = ["", "янв.", "фев.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сен.", "окт.", "ноя.", "дек."];
          const parts = assignData.time.split("-");
          formattedTime = `${parseInt(parts[2])} ${months[parseInt(parts[1])]} ${parts[0]}`;
        }
        this.responseAssignment = { ...assignData, isOverdue, formattedTime };

        // Работы студентов
        const subReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/submission/one/${this.assignmentId}`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if (!subReq.ok) throw new Error("Ошибка");
        const subsData = await subReq.json();

        subsData.sort((a, b) => {
          const dateA = new Date(a.created_at);
          const dateB = new Date(b.created_at);
          return dateA - dateB;
        });

        // Обработка дат сдачи
        if (this.responseAssignment.time) {
          const deadlineParts = this.responseAssignment.time.split(' ')[0].split('-'); 
          const deadlineDate = new Date(parseInt(deadlineParts[0]), parseInt(deadlineParts[1]) - 1, parseInt(deadlineParts[2])); 

          this.submissions = subsData.map(el => {
            if (!el.created_at) return el;
            const submitParts = el.created_at.split(' ')[0].split('-'); 
            const submitDate = new Date(parseInt(submitParts[0]), parseInt(submitParts[1]) - 1, parseInt(submitParts[2]));
            const isLate = submitDate > deadlineDate;
            
            const months = ["", "янв.", "фев.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сен.", "окт.", "ноя.", "дек."];
            const formattedTime = `${parseInt(submitParts[2])} ${months[parseInt(submitParts[1])]} ${submitParts[0]}`;

            return {
              ...el,
              isOverdue: isLate,
              formattedTime
            };
          });
        } else {
          this.submissions = subsData;
        }

      } catch (error) {
        console.error(error);
        this.message = "Ошибка загрузки данных";
      }
    },

    validateFileType(file) {
      const allowedExtensions = [
        'pdf', 
        'doc', 'docx', 
        'xls', 'xlsx', 'xlsm', 
        'ppt', 'pptx', 
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'
      ];
      
      const extension = file.name.split('.').pop().toLowerCase();
      
      if (!allowedExtensions.includes(extension)) {
        return false;
      }
      return true;
    },

    handleFileSelect(event) {
      const file = event.target.files[0];
      if (file) {
        // Проверка типа файла
        if (!this.validateFileType(file)) {
          this.fileError = 'Недопустимый формат! Разрешены только файлы Office, PDF и изображения.';
          // Сбрасываем выбор файла
          this.selectedFile = null;
          this.fileName = '';
          this.isFileChanged = false;
          event.target.value = ''; // Очищаем инпут, чтобы можно было выбрать тот же файл снова после исправления
          
          // Убираем ошибку через 4 секунды
          setTimeout(() => { this.fileError = ''; }, 4000);
          return;
        }

        this.fileError = ''; // Ошибок нет
        this.selectedFile = file;
        this.fileName = file.name;
        this.isFileChanged = true;
      }
    },

    OpenModalChange(id) {
      this.isOpen = true;
      this.fileError = '';
      const assignment = this.responseAssignment;
      if (assignment) {
        this.form.name = assignment.text; 
        this.form.time = assignment.time; 
        this.form.description = assignment.description || ''; 
        this.currentFileName = assignment.file ? assignment.file.substring(assignment.file.lastIndexOf('/') + 1) : '';
        this.fileName = `Загрузить новый (текущий: ${this.currentFileName})`;
        this.selectedFile = null;
        this.isFileChanged = false; 
      }
    },
    CloseModalChange() {
      this.isOpen = false;
      this.fileError = '';
      this.form = { name: '', time: '', description: '' };
      this.fileName = '';
      this.currentFileName = '';
      this.selectedFile = null;
    },

    async submitAnswer() {
      if (!this.form.name) return alert('Введите название');
      this.isLoading = true;
      const formData = new FormData();
      formData.append('text', this.form.name);
      formData.append('time', this.form.time);
      formData.append('description', this.form.description);
      formData.append('course_id', this.courseId);
      if (this.selectedFile) formData.append('file', this.selectedFile);
      formData.append('_method', 'PATCH'); 

      try {
        const headers = new Headers();
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
        const response = await fetch(`${process.env.VUE_APP_API_BASE_URL}/assignment/${this.assignmentId}`, {
          method: 'POST', headers: headers, body: formData
        });
        const result = await response.json();
        if (response.ok) {
          this.CloseModalChange();
          await this.loadData();
        } else {
          alert(result.message || 'Ошибка обновления');
        }
      } catch (e) { alert('Ошибка сети'); }
      finally { this.isLoading = false; }
    },

    validateScore(submission, event) {
      const val = event.target.value;
      
      // Разрешаем пустую строку (чтобы можно было стереть оценку)
      if (val === '') {
        submission.score = null;
        return;
      }

      const num = Number(val);

      // Если введено не число, игнорируем (оставляем старое значение)
      if (isNaN(num)) {
        event.target.value = submission.score || '';
        return;
      }

      // Ограничиваем диапазон от 2 до 5
      if (num < 2) {
        submission.score = 2;
        event.target.value = 2;
      } else if (num > 5) {
        submission.score = 5;
        event.target.value = 5;
      } else {
        submission.score = num;
      }

      // Отправляем на сервер только если значение изменилось и валидно
      this.changeScore(submission.id, submission.score);
    },

    // Метод нормализации при потере фокуса (если пользователь стер всё и ушел)
    normalizeScore(submission) {
      if (submission.score === null || submission.score === undefined || submission.score === '') {
        submission.score = null;
        // Можно также очистить поле визуально, если оценка удалена
        // Но лучше оставить как есть, если null допустим
      }
    },

    async changeScore(id, value) {
      // Если значение пустое или не число, не отправляем запрос (или отправляем null для удаления оценки)
      if (value === null || value === undefined) return;
      
      const formData = new FormData();
      formData.append('score', value);
      formData.append('course_id', this.courseId);
      formData.append('_method', 'PATCH'); 
      
      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/submission/updateScore/${id}`, {
          method: "POST", headers: headers, body: formData
        });
        if (!request.ok) {
          const err = await request.json().catch(() => ({}));
          alert(err.message || "Не удалось изменить оценку");
        }
      } catch (error) { console.error(error); }
    },

    async changeComment(id) {
      const submission = this.submissions.find(s => s.id === id);
      if (!submission) return;

      const formData = new FormData();
      formData.append('teacher_comment', submission.teacher_comment);
      formData.append('course_id', this.courseId);
      formData.append('_method', 'PATCH'); 
      const headers = new Headers();
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/submission/updateComment/${id}`, {
          method: "POST", headers: headers, body: formData
        });
        if (!request.ok) {
          const err = await request.json().catch(() => ({}));
          alert(err.message || "Не удалось сохранить комментарий");
        }
      } catch (error) { console.error(error); }
    }
  }
};
</script>

<style scoped>
/* Общие стили */
.review-page { font-family: 'Inter', sans-serif; background: #F8FAFC; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.main-layout { display: flex; gap: 2%; align-items: flex-start; }

/* Sidebar 24% */
.sidebar { width: 24%; flex-shrink: 0; }
.sidebar-content { background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: sticky; top: 20px; display: flex; flex-direction: column; gap: 20px; }
.page-title { font-size: 24px; color: #0B1C4F; margin: 0; font-weight: 700; }
.course-card-mini { background: #F8FAFC; border-radius: 15px; overflow: hidden; border: 1px solid #E2E8F0; }
.mini-cover { width: 100%; padding-top: 60%; position: relative; background: #E2E8F0; }
.mini-cover img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
.course-name { font-size: 18px; color: #0B1C4F; margin: 15px; font-weight: 600; line-height: 1.4; }
.sidebar-actions { display: flex; flex-direction: column; gap: 10px; }
.btn-sidebar, .btn-sidebar-outline { display: block; padding: 12px; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
.btn-sidebar { background: #EFF6FF; color: #0966F6; }
.btn-sidebar:hover { background: #DBEAFE; }
.btn-sidebar-outline { background: transparent; border: 1px solid #CBD5E1; color: #64748B; }
.btn-sidebar-outline:hover { border-color: #94A3B8; color: #334155; }

/* Content 74% */
.content-area { width: 74%; display: flex; flex-direction: column; gap: 25px; }

/* Карточка задания (Header) */
.assignment-header-card {
  background: #fff;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.04);
  border: 1px solid #F1F5F9;
}
.header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.assign-title { font-size: 24px; color: #0B1C4F; margin: 0; font-weight: 700; }
.deadline-badge { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: #DCFCE7; color: #16A34A; text-transform: uppercase; }
.deadline-badge.overdue { background: #FEE2E2; color: #DC2626; }
.assign-desc { color: #475569; line-height: 1.6; margin: 0 0 20px 0; font-size: 15px; }
.assign-meta-row { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; padding-top: 15px; border-top: 1px solid #F1F5F9; }
.meta-label { font-size: 14px; color: #64748B; font-weight: 600; }
.meta-value { font-size: 14px; font-weight: 700; }
.btn-file-small { background: #F8FAFC; color: #0966F6; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; border: 1px solid #E2E8F0; transition: 0.2s; }
.btn-file-small:hover { background: #EFF6FF; border-color: #0966F6; }
.btn-edit-small { background: transparent; color: #64748B; border: 1px solid #CBD5E1; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.btn-edit-small:hover { border-color: #94A3B8; color: #334155; background: #F1F5F9; }

/* Заголовок списка работ */
.submissions-header { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; }
.submissions-header h3 { font-size: 20px; color: #0B1C4F; margin: 0; }

/* Переключатель фильтра */
.filter-toggle {
  margin-left: auto;
  display: flex;
  background: #F1F5F9;
  padding: 4px;
  border-radius: 8px;
  gap: 4px;
}

.toggle-btn {
  border: none;
  background: transparent;
  padding: 6px 12px;
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-btn:hover {
  color: #334155;
  background: #E2E8F0;
}

.toggle-btn.active {
  background: #fff;
  color: #0966F6;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Адаптивность для заголовка */
@media (max-width: 900px) {
  .submissions-header {
    flex-wrap: wrap;
    gap: 15px;
  }
  .filter-toggle {
    margin-left: 0;
    width: 100%;
    justify-content: center;
  }
  .toggle-btn {
    flex: 1;
  }
}

.count-badge { background: #0966F6; color: #fff; font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }

/* Карточка работы студента */
.submission-card {
  background: #fff;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.04);
  border: 1px solid #F1F5F9;
  transition: transform 0.2s;
}
.submission-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }

.sub-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 15px; }
.student-info { display: flex; flex-direction: column; gap: 5px; }
.student-name { font-size: 18px; color: #0B1C4F; margin: 0; font-weight: 700; }
.sub-status { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.sub-date { font-weight: 400; color: #94A3B8; font-size: 12px; }

.btn-download {
  background: #EFF6FF; color: #0966F6;
  padding: 8px 16px; border-radius: 8px;
  text-decoration: none; font-size: 13px; font-weight: 600;
  transition: 0.2s; white-space: nowrap;
}
.btn-download:hover { background: #DBEAFE; }

.sub-body { display: flex; flex-direction: column; gap: 20px; background: #F8FAFC; padding: 20px; border-radius: 12px; }
.user-comment-block label, .teacher-review-block label {
  display: block; font-size: 12px; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;
}
.comment-text {
  background: #fff; padding: 12px; border-radius: 8px;
  border: 1px solid #E2E8F0; font-size: 14px; color: #334155;
  margin: 0; line-height: 1.5;
}

.teacher-review-block { display: flex; flex-direction: column; gap: 15px; }
.grade-input-group { display: flex; align-items: center; gap: 15px; }
.score-input {
  width: 60px; height: 40px;
  border: 2px solid #CBD5E1; border-radius: 8px;
  text-align: center; font-size: 18px; font-weight: 700; color: #0B1C4F;
  transition: 0.2s; outline: none;
}
.score-input:focus { border-color: #0966F6; box-shadow: 0 0 0 3px rgba(9, 102, 246, 0.1); }

.comment-input-group { display: flex; flex-direction: column; gap: 6px; }
.teacher-comment-input {
  width: 100%; border: 1px solid #CBD5E1; border-radius: 8px;
  padding: 10px; font-family: inherit; font-size: 14px;
  resize: vertical; background: #fff; transition: 0.2s; box-sizing: border-box;
}
.teacher-comment-input:focus { outline: none; border-color: #0966F6; box-shadow: 0 0 0 3px rgba(9, 102, 246, 0.1); }

.sub-footer { margin-top: 15px; text-align: right; }
.save-hint { font-size: 12px; color: #16A34A; font-weight: 600; }

/* Пустое состояние */
.empty-state { text-align: center; padding: 40px; background: #fff; border-radius: 16px; border: 2px dashed #CBD5E1; color: #64748B; }

/* Модальное окно (как у преподавателя в курсе) */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(4px); }
.modal-window { background: #fff; width: 100%; max-width: 500px; border-radius: 20px; overflow: hidden; animation: slideIn 0.3s ease; }
@keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-head { padding: 20px 25px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; }
.modal-head h3 { margin: 0; font-size: 20px; color: #0B1C4F; }
.close-x { background: none; border: none; font-size: 24px; cursor: pointer; color: #94A3B8; }
.close-x:hover { color: #334155; }
.modal-body { padding: 25px; display: flex; flex-direction: column; gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 14px; font-weight: 600; color: #475569; }
.form-input, .form-textarea { width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box; background: #F8FAFC; transition: 0.2s; }
.form-input:focus, .form-textarea:focus { outline: none; border-color: #0966F6; background: #fff; box-shadow: 0 0 0 3px rgba(9, 102, 246, 0.1); }
.form-textarea { resize: vertical; min-height: 80px; }
.file-upload-box { border: 2px dashed #CBD5E1; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; background: #F8FAFC; transition: 0.2s; }
.file-upload-box:hover { border-color: #0966F6; background: #EFF6FF; }
.upload-icon { font-size: 32px; margin-bottom: 8px; }
.file-hint { font-size: 14px; color: #64748B; margin: 0; }
.current-file { font-size: 12px; color: #16A34A; margin-top: 5px; font-weight: 600; }
.modal-foot { padding: 20px 25px; background: #F8FAFC; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; }
.btn-cancel { background: #fff; border: 1px solid #CBD5E1; color: #64748B; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.btn-cancel:hover { background: #F1F5F9; border-color: #94A3B8; }
.btn-confirm { background: #0966F6; color: #fff; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.btn-confirm:hover:not(:disabled) { background: #0759C9; transform: translateY(-1px); }
.btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 768px) {
  .main-layout { flex-direction: column; }
  .sidebar, .content-area { width: 100%; }
  .sidebar-content { position: static; }
  .sub-top { flex-direction: column; }
  .btn-download { width: 100%; text-align: center; box-sizing: border-box; }
  .assign-meta-row { flex-direction: column; align-items: flex-start; gap: 10px; }
  .btn-file-small, .btn-edit-small { width: 100%; text-align: center; }
}
/* Стиль для кнопки экспорта */
.btn-export-excel {
  background: #F0FDF4; /* Светло-зеленый фон */
  color: #16A34A;      /* Темно-зеленый текст */
  border: 1px solid #86EFAC;
  padding: 12px;
  border-radius: 10px;
  text-align: center;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-export-excel:hover {
  background: #DCFCE7;
  border-color: #16A34A;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(22, 163, 74, 0.1);
}

/* Убираем стрелочки у input type="number" */
.score-input::-webkit-outer-spin-button,
.score-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Для Firefox */
.score-input[type=number] {
  -moz-appearance: textfield;
}

/* Подсветка блока загрузки при ошибке */
.upload-error {
  border-color: #DC2626 !important;
  background-color: #FEF2F2 !important;
  animation: shake 0.3s ease-in-out;
}

/* Текст ошибки */
.error-text {
  color: #DC2626;
  font-size: 12px;
  font-weight: 600;
  text-align: left;
  padding-left: 5px;
  margin-top: 5px;
  margin-bottom: 0;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
</style>