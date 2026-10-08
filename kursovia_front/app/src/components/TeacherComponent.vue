<template>
  <main class="course-page">
    <div class="container">
      <div class="main-layout">
        
        <aside class="sidebar">
          <div class="sidebar-content">
            <h1 class="page-title">{{ message || 'Управление' }}</h1>
            
            <div class="course-card-mini" v-if="response.name">
              <div class="mini-cover">
                <img :src="courseImages[response.img_id] || courseImages[1]" alt="Обложка">
              </div>
              <h3 class="course-name">{{ response.name }}</h3>
              
              <div class="access-code-box" @click="copyAccessCode" title="Нажмите, чтобы скопировать">
                <span class="code-label">Код доступа:</span>
                <div class="code-wrapper">
                  <span class="code-value">{{ response.access_code }}</span>
                </div>
              </div>
            </div>

            <div class="sidebar-actions">
              <router-link :to="'/posts/' + response.id" class="btn-sidebar">
                Посты курса
              </router-link>
              <router-link to="/courses" class="btn-sidebar-outline">
                ← Назад к списку
              </router-link>

              <button 
                @click.stop="handleDeleteClick" 
                class="btn-delete-course" 
                :class="{ 'is-confirming': confirmDelete }"
              >
                <span v-if="!confirmDelete">
                  Удалить курс
                </span>
                <span v-else>
                  Нажмите еще раз!
                </span>
              </button>
            </div>
          </div>
        </aside>

        <section class="content-area">
          <div class="section-header">
            <div class="header-left">
              <h2>Задания курса</h2>
              <!-- ГРАМОТНОЕ ОТОБРАЖЕНИЕ КОЛИЧЕСТВА ПОЛЬЗОВАТЕЛЕЙ -->
              <span class="students-badge" :title="`${studentsCount} студент(ов) в курсе`">
                Студентов в курсе: {{ studentsCount }}
              </span>
            </div>
            <button @click="OpenModalCreate()" class="btn-primary">
              Добавить задание
            </button>
          </div>

          <div v-if="responseAssignment.length === 0" class="empty-state">
            <h3>Заданий еще нет</h3>
            <p>Нажмите кнопку выше, чтобы создать первое задание для студентов.</p>
          </div>

          <div v-for="assignment in responseAssignment" :key="assignment.id" class="assignment-card">
            <div class="card-top">
              <h3 class="assign-title">{{ assignment.text }}</h3>
              <div class="deadline-tag" :class="{ overdue: assignment.isOverdue }">
                {{ assignment.isOverdue ? 'Просрочено' : 'Срок' }}
              </div>
            </div>
            
            <p class="assign-desc">{{ assignment.description }}</p>
            
            <div class="assign-file">
              <button disabled class="file-link-disabled" v-if="getFileName(assignment.file) == 0">Файл не прикреплен</button>
              <a v-else :href="'http://classman24.ru/storage/' + assignment.file" target="_blank" class="file-link">
                <strong>Файл задания:</strong> {{ getFileName(assignment.file) }}
              </a>
            </div>

            <div class="assign-meta">
              <span class="date-text" :style="{ color: assignment.isOverdue ? '#DC2626' : '#16A34A' }">
                Срок сдачи: {{ assignment.formattedTime }}
              </span>
            </div>

            <div class="assign-actions">
              <button @click="OpenModalChange(assignment.id)" class="btn-action edit">
                Изменить
              </button>
              <button @click="DeleteAssignment(assignment.id)" class="btn-action delete">
                Удалить
              </button>
              <router-link 
                :to="'/checkSubmissionsTeacherView/' + response.id + '/' + assignment.id" 
                class="btn-action view"
              >
                Посмотреть ответы
              </router-link>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Модальное окно: ИЗМЕНИТЬ -->
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
            <div class="file-upload-box" @click="$refs.fileInput.click()" :class="{ 'upload-error': fileError }">
              <!-- Добавлен атрибут accept -->
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileSelect" 
                hidden 
                accept=".pdf,.doc,.docx,.xls,.xlsx,.xlsm,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.svg" 
              />
              <p class="file-hint">{{ fileName || 'Нажмите, чтобы выбрать новый файл' }}</p>
              <p v-if="fileName && !isFileChanged" class="current-file">Текущий: {{ currentFileName }}</p>
            </div>
            <!-- Вывод ошибки -->
            <p v-if="fileError" class="error-text">{{ fileError }}</p>
          </div>
        </div>
        <div class="modal-foot">
          <button @click="CloseModalChange" class="btn-cancel">Отмена</button>
          <button @click="submitAnswer" :disabled="isLoading" class="btn-confirm">
            {{ isLoading ? 'Сохранение...' : 'Сохранить изменения' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Модальное окно: СОЗДАТЬ -->
    <div v-if="isOpenCreate" class="modal-overlay" @click.self="CloseModalCreate">
      <div class="modal-window">
        <div class="modal-head">
          <h3>Новое задание</h3>
          <button @click="CloseModalCreate" class="close-x">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Название *</label>
            <input type="text" v-model="formCreate.nameCreate" placeholder="Например: Лабораторная работа №1" class="form-input" />
          </div>
          <div class="form-group">
            <label>Срок сдачи *</label>
            <input type="date" v-model="formCreate.timeCreate" class="form-input" :min="todayDate"/>
          </div>
          <div class="form-group">
            <label>Описание *</label>
            <textarea v-model="formCreate.descriptionCreate" placeholder="Что нужно сделать?" class="form-textarea"></textarea>
          </div>
          <div class="form-group">
            <label>Файл задания</label>
            <div class="file-upload-box" @click="$refs.fileInputCreate.click()" :class="{ 'upload-error': fileError }">
              <!-- Добавлен атрибут accept -->
              <input 
                type="file" 
                ref="fileInputCreate" 
                @change="handleFileSelectCreate" 
                hidden 
                accept=".pdf,.doc,.docx,.xls,.xlsx,.xlsm,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.svg" 
              />
              <p class="file-hint">{{ fileNameCreate || 'Нажмите, чтобы выбрать файл' }}</p>
            </div>
            <!-- Вывод ошибки -->
            <p v-if="fileError" class="error-text">{{ fileError }}</p>
          </div>
        </div>
        <div class="modal-foot">
          <button @click="CloseModalCreate" class="btn-cancel">Отмена</button>
          <button @click="submitAnswerCreate" :disabled="isLoadingCreate || !formCreate.nameCreate" class="btn-confirm">
            {{ isLoadingCreate ? 'Создание...' : 'Создать задание' }}
          </button>
        </div>
      </div>
    </div>
    <!-- Всплывающее уведомление -->
    <transition name="fade-slide">
      <div v-if="showCopyNotification" class="copy-toast">
        <span class="toast-text">Код скопирован в буфер!</span>
      </div>
    </transition>
    <transition name="fade-slide">
      <div v-if="showErrorNotification" class="error-toast">
        <span class="toast-text">{{ErrorNotificationText}}</span>
      </div>
    </transition>
    <transition name="fade-slide">
      <div v-if="showApplyNotification" class="copy-toast">
        <span class="toast-text">{{ ApplyNotificationText }}</span>
      </div>
    </transition>
  </main>
</template>

<script>
export default {
  name: 'TeacherCourseView',
  data() {
    return {
      isOpen: false,
      isLoading: false,
      fileName: '',
      currentFileName: '',
      selectedFile: null,
      assignmentId: '',
      isFileChanged: false, 

      isOpenCreate: false,
      isLoadingCreate: false,
      fileNameCreate: '',
      selectedFileCreate: null,
      isFileChangedCreate: false, 

      form: { name: "", time: "", description: "" },
      formCreate: { nameCreate: "", timeCreate: "", descriptionCreate: "" },

      user_id: "",
      response: {},
      responseAssignment: [],
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
      message: "",

      showCopyNotification: false,

      showErrorNotification: false,
      ErrorNotificationText: "",

      showApplyNotification: false,
      ApplyNotificationText: "",

      copyTimer: null,

      confirmDelete: false, 
      deleteTimer: null,

      fileError: '',

      studentsCount: 0, 
    };
  },
  async mounted() {
    await this.loadCourseData();
  },
  computed: {
    todayDate() {
      const date = new Date();
      const offset = date.getTimezoneOffset() * 60000;
      const localISOTime = (new Date(date - offset)).toISOString().slice(0, 10);
      return localISOTime;
    }
  },
  methods: {
    handleDeleteClick() {
      if (this.confirmDelete) {
        // Если уже нажали первый раз — удаляем
        this.executeDelete();
      } else {
        // Если первый клик — включаем режим подтверждения
        this.confirmDelete = true;
        
        // Сброс через 5 секунд, если пользователь не нажмет повторно
        if (this.deleteTimer) clearTimeout(this.deleteTimer);
        this.deleteTimer = setTimeout(() => {
          this.confirmDelete = false;
        }, 5000);
      }
    },

    // Метод самого удаления (логика осталась той же)
    async executeDelete() {
      try {
        const headers = new Headers();
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
        
        const response = await fetch(`${process.env.VUE_APP_API_BASE_URL}/courses/${this.courseId}`, {
          method: "DELETE",
          headers: headers
        });

        if (response.ok) {
          alert("Курс успешно удален!");
          this.$router.push("/courses");
        } else {
          const err = await response.json();
          alert(err.message || "Не удалось удалить курс");
          this.confirmDelete = false; // Сброс при ошибке
        }
      } catch (e) {
        console.error(e);
        alert("Ошибка сети при удалении");
        this.confirmDelete = false;
      }
    },

    // Метод для сброса подтверждения при клике вне кнопки (опционально)
    resetConfirm() {
      this.confirmDelete = false;
      if (this.deleteTimer) clearTimeout(this.deleteTimer);
    },

    async copyAccessCode() {
      const code = this.response.access_code;
      if (!code) return;

      try {
        // Современный API буфера обмена
        await navigator.clipboard.writeText(code);
        
        // Показываем уведомление
        this.showCopyNotification = true;
        
        // Скрываем через 2 секунды
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showCopyNotification = false;
        }, 2000);
        
      } catch (err) {
        // Фолбэк для старых браузеров (если вдруг не сработает)
        const textArea = document.createElement("textarea");
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("Copy");
        textArea.remove();
        
        this.showCopyNotification = true;
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showCopyNotification = false;
        }, 2000);
      }
    },
    getFileName(path) {
      if (!path) return 0;
      return path.substring(path.lastIndexOf('/') + 1);
    },
    async loadCourseData() {
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        // Курс
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/course`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if (!courseReq.ok) throw new Error("Нет доступа");
        this.response = await courseReq.json();

        try {
          const countReq = await fetch(
            `${process.env.VUE_APP_API_BASE_URL}/courses/students/count`, 
            {
              method: "POST",
              headers: headers,
              body: JSON.stringify({ course_id: this.courseId })
            }
          );
          if (countReq.ok) {
            const countData = await countReq.json();
            this.studentsCount = countData.count || 0;
          }
        } catch (e) {
          console.warn("Не удалось загрузить количество студентов:", e);
          this.studentsCount = 0;
        }

        // Пользователь
        const userReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/users`, {
          method: "POST",
          headers: headers
        });
        if (!userReq.ok) throw new Error("Ошибка");
        this.user_id = await userReq.json();

        if (this.user_id != this.response.user_id) {
          window.location.href = `/`;
          return;
        }

        // Задания
        const assignReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/assignment`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if (!assignReq.ok) throw new Error("Ошибка");
        const assignments = await assignReq.json();

        this.responseAssignment = assignments.map(el => {
          const now = new Date(); now.setHours(0,0,0,0);
          const d = el.time ? new Date(el.time) : null;
          if(d) d.setHours(0,0,0,0);
          const isOverdue = d && d < now;
          
          let formattedTime = 'Нет срока';
          if (el.time) {
            const months = ["", "янв.", "фев.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сен.", "окт.", "ноя.", "дек."];
            const parts = el.time.split("-");
            formattedTime = `${parseInt(parts[2])} ${months[parseInt(parts[1])]} ${parts[0]}`;
          }
          return { ...el, isOverdue, formattedTime };
        });

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
          this.fileError = 'Разрешены только файлы Office (Word, Excel, PowerPoint), изображения и PDF.';
          // Сбрасываем выбор файла
          this.selectedFile = null;
          this.fileName = '';
          this.isFileChanged = false;
          event.target.value = ''; // Очищаем инпут
          
          // Убираем ошибку через 3 секунды
          setTimeout(() => { this.fileError = ''; }, 4000);
          return;
        }

        this.fileError = ''; // Ошибок нет
        this.selectedFile = file;
        this.fileName = file.name;
        this.isFileChanged = true;
      }
    },
    
    handleFileSelectCreate(event) {
      const file = event.target.files[0];
      if (file) {
        // Проверка типа файла
        if (!this.validateFileType(file)) {
          this.fileError = 'Разрешены только файлы Office, изображения и PDF.';
          this.selectedFileCreate = null;
          this.fileNameCreate = '';
          this.isFileChangedCreate = false;
          event.target.value = '';
          
          setTimeout(() => { this.fileError = ''; }, 4000);
          return;
        }

        this.fileError = '';
        this.selectedFileCreate = file;
        this.fileNameCreate = file.name;
        this.isFileChangedCreate = true;
      }
    },

    OpenModalChange(id) {
      this.isOpen = true;
      this.assignmentId = id;
      const assignment = this.responseAssignment.find(a => a.id === id);
      
      if (assignment) {
        this.form.name = assignment.text; 
        this.form.time = assignment.time; 
        this.form.description = assignment.description || ''; 
        this.currentFileName = this.getFileName(assignment.file);
        this.fileName = `Загрузить новый файл (текущий: ${this.currentFileName})`;
        this.selectedFile = null;
        this.isFileChanged = false; 
      }
    },
    CloseModalChange() {
      this.isOpen = false;
      this.form = { name: '', time: '', description: '' };
      this.fileName = '';
      this.currentFileName = '';
      this.selectedFile = null;
    },

    OpenModalCreate() {
      this.isOpenCreate = true;
      this.formCreate = { nameCreate: '', timeCreate: '', descriptionCreate: '' };
      this.fileNameCreate = '';
      this.selectedFileCreate = null;
    },
    CloseModalCreate() {
      this.isOpenCreate = false;
      this.formCreate = { nameCreate: '', timeCreate: '', descriptionCreate: '' };
      this.fileNameCreate = '';
      this.selectedFileCreate = null;
    },

    async submitAnswer() {
      if (!this.form.name){
        this.showErrorNotification = true;
        this.ErrorNotificationText = "Введите название"
        
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showErrorNotification = false;
        }, 2000);
        return;
      };
      if (!this.form.time){
        this.showErrorNotification = true;
        this.ErrorNotificationText = "Введите время"
        
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showErrorNotification = false;
        }, 2000);
        return;
      };
      if (!this.form.description) {
        this.showErrorNotification = true;
        this.ErrorNotificationText = "Введите описане"
        
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showErrorNotification = false;
        }, 2000);
        return;
      };
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

          this.showApplyNotification = true;
          this.ApplyNotificationText = "Задание изменено"
          
          if (this.copyTimer) clearTimeout(this.copyTimer);
          this.copyTimer = setTimeout(() => {
            this.showApplyNotification = false;
          }, 2000);

          this.CloseModalChange();
          await this.loadCourseData();
        } else {

          this.showErrorNotification = true;
          this.ErrorNotificationText = result.message || "Ошибка обновления"
          
          if (this.copyTimer) clearTimeout(this.copyTimer);
          this.copyTimer = setTimeout(() => {
            this.showErrorNotification = false;
          }, 2000);

        }
      } catch (e) { alert('Ошибка сети'); }
      finally { this.isLoading = false; }
    },

    async submitAnswerCreate() {
      if (!this.formCreate.nameCreate){
        this.showErrorNotification = true;
        this.ErrorNotificationText = "Введите название"
        
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showErrorNotification = false;
        }, 2000);
        return;
      };
      if (!this.formCreate.timeCreate){
        this.showErrorNotification = true;
        this.ErrorNotificationText = "Введите время"
        
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showErrorNotification = false;
        }, 2000);
        return;
      };
      if (!this.formCreate.descriptionCreate) {
        this.showErrorNotification = true;
        this.ErrorNotificationText = "Введите описане"
        
        if (this.copyTimer) clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showErrorNotification = false;
        }, 2000);
        return;
      };
      this.isLoadingCreate = true;
      const formData = new FormData();
      formData.append('text', this.formCreate.nameCreate);
      formData.append('time', this.formCreate.timeCreate);
      formData.append('description', this.formCreate.descriptionCreate);
      formData.append('course_id', this.courseId);
      if (this.selectedFileCreate) formData.append('file', this.selectedFileCreate);

      try {
        const headers = new Headers();
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
        const response = await fetch(`${process.env.VUE_APP_API_BASE_URL}/assignment/${this.courseId}`, {
          method: 'POST', headers: headers, body: formData
        });
        const textResult = await response.text();
        let result = {};
        try { result = textResult ? JSON.parse(textResult) : {}; } catch(e){}
        
        if (response.ok) {
          this.showApplyNotification = true;
          this.ApplyNotificationText = "Задание создано"
          
          if (this.copyTimer) clearTimeout(this.copyTimer);
          this.copyTimer = setTimeout(() => {
            this.showApplyNotification = false;
          }, 2000);
          this.CloseModalCreate();
          await this.loadCourseData();
        } else {
          this.showErrorNotification = true;
          this.ErrorNotificationText = result.message || "Ошибка создания"
          
          if (this.copyTimer) clearTimeout(this.copyTimer);
          this.copyTimer = setTimeout(() => {
            this.showErrorNotification = false;
          }, 2000);
        }
      } catch (e) { alert('Ошибка сети'); }
      finally { this.isLoadingCreate = false; }
    },

    async DeleteAssignment(id){
      if(!confirm("Удалить задание? Это действие нельзя отменить.")) return;
      try {
        const headers = new Headers();
        headers.append("Content-Type", "application/json");
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
        const request = await fetch(`${process.env.VUE_APP_API_BASE_URL}/assignment/${id}`, {
          method: "DELETE",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });
        if(request.ok) await this.loadCourseData();
        else alert("Не удалось удалить");
      } catch(e) { console.error(e); alert("Ошибка"); }
    }
  },
  beforeDestroy() {
    // Очистка таймера при уходе со страницы
    if (this.deleteTimer) clearTimeout(this.deleteTimer);
  }
};
</script>

<style scoped>
.upload-error {
  border-color: #DC2626 !important;
  background-color: #FEF2F2 !important;
  animation: shake 0.3s ease-in-out;
}

/* Текст ошибки */
.error-text {
  color: #DC2626;
  font-size: 12px;
  margin-top: 5px;
  font-weight: 600;
  text-align: left;
  padding-left: 5px;
}

/* Анимация тряски (если у вас её еще нет) */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
/* Общие стили (как у студента) */
.course-page { font-family: 'Inter', sans-serif; background: #F8FAFC; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.main-layout { display: flex; gap: 2%; align-items: flex-start; }

/* Sidebar 24% */
.sidebar { width: 24%; flex-shrink: 0; }
.sidebar-content {background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: sticky; top: 20px; display: flex; flex-direction: column; gap: 20px; }
.page-title { font-size: 24px; color: #0B1C4F; margin: 0; font-weight: 700; }
.course-card-mini { background: #F8FAFC; border-radius: 15px; overflow: hidden; border: 1px solid #E2E8F0; }
.mini-cover { width: 100%; padding-top: 60%; position: relative; background: #E2E8F0; }
.mini-cover img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
.course-name { font-size: 18px; color: #0B1C4F; margin: 15px; font-weight: 600; line-height: 1.4; }
.access-code-box { background: #fff; margin: 0 15px 15px; padding: 10px; border-radius: 8px; border: 1px dashed #CBD5E1; text-align: center; }
.code-label { display: block; font-size: 11px; color: #64748B; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
.code-value { font-size: 18px; color: #0966F6; font-weight: 800; letter-spacing: 1px; }
.sidebar-actions { display: flex; flex-direction: column; gap: 10px; }
.btn-sidebar, .btn-sidebar-outline { display: block; padding: 12px; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
.btn-sidebar { background: #EFF6FF; color: #0966F6; }
.btn-sidebar:hover { background: #DBEAFE; }
.btn-sidebar-outline { background: transparent; border: 1px solid #CBD5E1; color: #64748B; }
.btn-sidebar-outline:hover { border-color: #94A3B8; color: #334155; }

/* Content 74% */
.content-area { width: 74%; display: flex; flex-direction: column; gap: 20px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 15px; }
.section-header h2 { font-size: 28px; color: #0B1C4F; margin: 0; }
.btn-primary { background: #0966F6; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary:hover { background: #0759C9; transform: translateY(-2px); }

/* Карточка задания */
.assignment-card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #F1F5F9; transition: transform 0.2s; }
.assignment-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; margin-bottom: 15px; }
.assign-title { font-size: 20px; color: #0B1C4F; margin: 0; font-weight: 700; }
.deadline-tag { background: #DCFCE7; color: #16A34A; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.deadline-tag.overdue { background: #FEE2E2; color: #DC2626; }
.assign-desc { color: #475569; line-height: 1.6; margin: 0 0 15px 0; font-size: 15px; }
.assign-file { margin-bottom: 15px; }
.file-link { display: inline-block; background: #F8FAFC; padding: 8px 12px; border-radius: 8px; color: #0966F6; text-decoration: none; font-size: 14px; border: 1px solid #E2E8F0; transition: 0.2s; }
.file-link:hover { background: #EFF6FF; border-color: #0966F6; }
.file-link-disabled{display: inline-block; background: #F8FAFC; padding: 8px 12px; border-radius: 8px; color: #0966F6; text-decoration: none; font-size: 14px; border: 1px solid #E2E8F0; transition: 0.2s; opacity: .7;}
.assign-meta { margin-bottom: 20px; }
.date-text { font-size: 14px; font-weight: 600; }
.assign-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-action { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.btn-action.edit { background: #EFF6FF; color: #0966F6; }
.btn-action.edit:hover { background: #DBEAFE; }
.btn-action.delete { background: #FEF2F2; color: #DC2626; }
.btn-action.delete:hover { background: #FEE2E2; }
.btn-action.view { background: #F0FDF4; color: #16A34A; }
.btn-action.view:hover { background: #DCFCE7; }

/* Пустое состояние */
.empty-state { text-align: center; padding: 60px; background: #fff; border-radius: 16px; border: 2px dashed #CBD5E1; color: #64748B; }
.empty-icon { font-size: 48px; margin-bottom: 15px; }
.empty-state h3 { margin: 0 0 10px 0; color: #334155; }

/* Модальные окна */
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
  .section-header { flex-direction: column; align-items: stretch; }
  .btn-primary { justify-content: center; }
}

/* Делаем блок с кодом похожим на кнопку */
.access-code-box {
  background: #fff;
  margin: 0 15px 15px;
  padding: 10px;
  border-radius: 8px;
  border: 1px dashed #CBD5E1;
  text-align: center;
  cursor: pointer; /* Курсор-рука */
  transition: all 0.2s ease;
  position: relative;
}

.access-code-box:hover {
  background: #EFF6FF;
  border-color: #0966F6;
  transform: translateY(-1px);
}

.code-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.copy-icon {
  font-size: 14px;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.access-code-box:hover .copy-icon {
  opacity: 1;
  color: #0966F6;
}

/* Стили для всплывающего уведомления (Toast) */
.copy-toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  background: #10B981; /* Зеленый цвет успеха */
  color: #fff;
  padding: 12px 24px;
  border-radius: 50px;
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 10000;
  font-weight: 600;
  font-size: 14px;
  pointer-events: none;
}

.error-toast{
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  background: #b91010; /* Зеленый цвет успеха */
  color: #fff;
  padding: 12px 24px;
  border-radius: 50px;
  box-shadow: 0 10px 25px rgba(185, 22, 16, 0.4);
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 10000;
  font-weight: 600;
  font-size: 14px;
  pointer-events: none;
}

.toast-icon {
  font-size: 16px;
}

/* Анимация появления/исчезновения */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(10px);
}

.btn-delete-course {
  background: #FEF2F2;
  color: #DC2626;
  border: 1px solid #FCA5A5;
  padding: 10px;
  border-radius: 10px;
  text-align: center;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 10px;
  width: 100%; /* Чтобы кнопка была во всю ширину */
  display: flex;
  justify-content: center;
  align-items: center;
}

.btn-delete-course:hover {
  background: #FEE2E2;
  border-color: #EF4444;
}

/* Стили для режима подтверждения */
.btn-delete-course.is-confirming {
  background: #DC2626;
  color: #fff;
  border-color: #B91C1C;
  animation: pulse-danger 1s infinite;
}

.btn-delete-course.is-confirming:hover {
  background: #B91C1C;
  transform: scale(1.02);
}

.header-left {
  display: flex;
  align-items: end;
  gap: 12px;
  flex-wrap: wrap;
}

.students-badge {
  background: #EFF6FF;
  color: #0966F6;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #DBEAFE;
  transition: all 0.2s ease;
}

.students-badge:hover {
  background: #DBEAFE;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(9, 102, 246, 0.15);
}

@keyframes pulse-danger {
  0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
  100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}
</style>