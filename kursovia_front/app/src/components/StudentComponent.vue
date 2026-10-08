<template>
  <main class="course-page">
    <div class="container">
      <div class="main-layout">
        
        <aside class="sidebar">
          <div class="sidebar-content">
            <h1 class="page-title">{{ message || 'Курс' }}</h1>
            
            <div class="course-card-mini" v-if="response.name">
              <div class="mini-cover">
                <img :src="courseImages[response.img_id] || courseImages[1]" alt="Обложка">
              </div>
              <h3 class="course-name">{{ response.name }}</h3>
            </div>

            <div class="sidebar-actions">
              <router-link :to="'/posts/' + response.id" class="btn-sidebar">
                Посты курса
              </router-link>
              <router-link to="/courses" class="btn-sidebar-outline">
                ← Назад к списку
              </router-link>
              
              <!-- КНОПКА ОТПИСКИ -->
              <button 
                @click="handleLeaveClick" 
                class="btn-leave-course" 
                :class="{ 'is-confirming': confirmLeave }"
              >
                <span v-if="!confirmLeave">
                  Отписаться от курса
                </span>
                <span v-else>
                  Нажмите еще раз!
                </span>
              </button>
            </div>

            <GradeChart 
              :submissions="responseSubmission" 
              :assignments="responseAssignment" 
            />
          </div>
        </aside>

        <!-- Остальной контент без изменений... -->
        <section class="content-area">
          <!-- ... (твой существующий код заданий) ... -->
           <div class="section-header">
            <h2>Задания</h2>
            <span class="badge-count">{{ responseAssignment.length }}</span>
          </div>
          <!-- Для краткости я скрыл повторяющийся код карточек, он остается без изменений -->
          <div v-if="responseAssignment.length === 0" class="empty-state">
            <h3>Заданий пока нет</h3>
            <p>Преподаватель еще не добавил задания.</p>
          </div>
          <div v-for="assignment in responseAssignment" :key="assignment.id" class="assignment-card">
             <!-- Твой код карточки задания -->
             <div class="card-top">
                <h3 class="assign-title">{{ assignment.text }}</h3>
                <div class="deadline-tag" :class="{ overdue: assignment.isOverdue }">
                  {{ assignment.isOverdue ? 'Просрочено' : 'Срок' }}
                </div>
              </div>
              <p class="assign-desc">{{ assignment.description }}</p>
              <div class="assign-meta">
                <span class="date-text" :style="{ color: assignment.isOverdue ? '#DC2626' : '#16A34A' }">
                  {{ assignment.formattedTime }}
                </span>
              </div>
              <div class="assign-actions">
                <button disabled class="file-link-disabled" v-if="getFileName(assignment.file) == 0">Файл не прикреплен</button>
                <a v-else :href="'http://classman24.ru/storage/' + assignment.file" target="_blank" class="btn-link-small">Файл задания</a>
                
                <!-- ✅ ИЗМЕНЕНО: Условие теперь проверяет оценку -->
                <button 
                  v-if="getSubmission(assignment.id)" 
                  @click="DeleteAnswer(getSubmission(assignment.id))" 
                  :disabled="assignment.isOverdue || (getSubmission(assignment.id).score !== null && getSubmission(assignment.id).score !== '' && getSubmission(assignment.id).score != 2)"
                  :class="(assignment.isOverdue || (getSubmission(assignment.id).score !== null && getSubmission(assignment.id).score !== '' && getSubmission(assignment.id).score != 2)) ? 'btn-delete-disabled' : 'btn-delete-small'"
                >
                  <!-- Логика текста кнопки -->
                  <span v-if="assignment.isOverdue">Удаление запрещено</span>
                  <span v-else-if="getSubmission(assignment.id).score !== null && getSubmission(assignment.id).score !== '' && getSubmission(assignment.id).score != 2">
                    Оценка выставлена ({{ getSubmission(assignment.id).score }})
                  </span>
                  <span v-else>Удалить ответ</span>
                </button>

                <button v-else-if="assignment.isOverdue" class="btn-submit-disabled" disabled>Срок сдачи истек</button>
                <button v-else @click="OpenModalAnswer(assignment.id)" class="btn-submit">Сдать работу</button>
              </div>
              <div v-if="getSubmission(assignment.id)" class="submission-result">
                <div class="sub-header"><h4>Ваша работа</h4><span class="status-ok">Сдано</span></div>
                <div class="sub-body">
                  <div class="sub-row file-row" v-if="getSubmission(assignment.id).file">
                    <label>Ваш файл:</label>
                    <div class="file-display">
                      <span class="file-name-text">{{ getFileName(getSubmission(assignment.id).file) }}</span>
                      <a :href="'http://classman24.ru/storage/' + getSubmission(assignment.id).file" target="_blank" class="btn-open-file">Открыть файл</a>
                    </div>
                  </div>
                  <div class="sub-row file-row" v-else><label>Файл:</label><span class="no-file-text">Файл не прикреплен</span></div>
                  <div class="sub-row"><label>Ваш комментарий:</label><p>{{ getSubmission(assignment.id).user_comment || '—' }}</p></div>
                  <div v-if="getSubmission(assignment.id).teacher_comment" class="sub-row teacher"><label>Преподаватель:</label><p>{{ getSubmission(assignment.id).teacher_comment }}</p></div>
                  <div class="grade-row" v-if="getSubmission(assignment.id).score !== null && getSubmission(assignment.id).score !== ''"><span>Оценка:</span><div class="grade-circle">{{ getSubmission(assignment.id).score }}</div></div>
                  <div v-else-if="getSubmission(assignment.id).teacher_comment" class="grade-row waiting"><span>Ожидает оценки</span></div>
                </div>
              </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Модалка сдачи (без изменений) -->
    <div v-if="isOpen" class="modal-overlay" @click.self="CloseModalAnswer">
      <div class="modal-window">
        <div class="modal-head"><h3>Сдать задание</h3><button @click="CloseModalAnswer" class="close-x">✕</button></div>
        <div class="modal-body">
          <div class="upload-box" @click="$refs.fileInput.click()" :class="{ 'upload-error': fileError }">
            <input type="file" ref="fileInput" @change="handleFileSelect" hidden accept=".pdf,.doc,.docx,.xls,.xlsx,.xlsm,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.svg" />
            <div class="upload-icon">📁</div>
            <p v-if="fileName" class="file-name">{{ fileName }}</p>
            <p v-else class="upload-hint">Нажмите, чтобы выбрать файл</p>
            <p class="upload-subhint">Только Office, PDF и изображения</p>
          </div>
          <p v-if="fileError" class="error-text">{{ fileError }}</p>
          <textarea v-model="userComment" placeholder="Комментарий..." class="modal-textarea"></textarea>
        </div>
        <div class="modal-foot">
          <button @click="CloseModalAnswer" class="btn-cancel">Отмена</button>
          <button @click="submitAnswer" :disabled="isLoading || !selectedFile" class="btn-confirm">{{ isLoading ? '...' : 'Отправить' }}</button>
        </div>
      </div>
    </div>
    <transition name="fade-slide">
      <div v-if="showApplyNotification" class="copy-toast">
        <span class="toast-text">{{ ApplyNotificationText }}</span>
      </div>
    </transition>
  </main>
</template>

<script>
import GradeChart from '@/components/graphs/GradeChart.vue';

export default {
  name: 'StudentCourseView',
  components: { GradeChart },
  data() {
    return {
      isOpen: false,
      isLoading: false,
      fileName: '',
      selectedFile: null,
      userComment: '',
      assignmentId: '',
      fileError: '',
      user_id: "",
      response: {},
      responseAssignment: [],
      responseSubmission: [],
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

      confirmLeave: false,
      leaveTimer: null,

      showApplyNotification: false,
      ApplyNotificationText: "",
      Timer: null,
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
    handleLeaveClick() {
      if (this.confirmLeave) {
        // Если уже нажали первый раз — выполняем удаление
        this.executeLeave();
      } else {
        // Если первый клик — включаем режим подтверждения
        this.confirmLeave = true;
        
        // Сброс через 5 секунд, если пользователь передумал
        if (this.leaveTimer) clearTimeout(this.leaveTimer);
        this.leaveTimer = setTimeout(() => {
          this.confirmLeave = false;
        }, 5000);
      }
    },

    // Метод самого запроса на сервер
    async executeLeave() {
      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/course/leave`, {
          method: "DELETE",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });

        const data = await res.json();

        if (res.ok) {
          alert(data.message || "Вы успешно отписались от курса.");
          this.$router.push("/courses");
        } else {
          alert(data.message || "Ошибка при отписке.");
          this.confirmLeave = false; // Сброс при ошибке
        }
      } catch (e) {
        console.error(e);
        alert("Произошла ошибка сети.");
        this.confirmLeave = false;
      } finally {
        if (this.leaveTimer) clearTimeout(this.leaveTimer);
      }
    },
    async leaveCourse() {
      if (!confirm(`Вы уверены, что хотите отписаться от курса "${this.response.name}"?\nВсе ваши сданные работы и прогресс будут потеряны.`)) {
        return;
      }

      const headers = new Headers();
      headers.append("Content-Type", "application/json");
      headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);

      try {
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/course/leave`, {
          method: "POST",
          headers: headers,
          body: JSON.stringify({ course_id: this.courseId })
        });

        const data = await res.json();

        if (res.ok) {
          alert(data.message || "Вы успешно отписались от курса.");
          // Перенаправляем на список курсов
          this.$router.push("/courses");
        } else {
          alert(data.message || "Ошибка при отписке.");
        }
      } catch (e) {
        console.error(e);
        alert("Произошла ошибка сети.");
      }
    },
    beforeDestroy() {
      if (this.leaveTimer) clearTimeout(this.leaveTimer);
    },

    validateFileType(file) {
      const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'xlsm', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
      const extension = file.name.split('.').pop().toLowerCase();
      return allowedExtensions.includes(extension);
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
        const courseReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/course`, { method: "POST", headers: headers, body: JSON.stringify({ course_id: this.courseId }) });
        if (!courseReq.ok) throw new Error("Нет доступа к курсу");
        this.response = await courseReq.json();
        const userReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/users`, { method: "POST", headers: headers });
        if (!userReq.ok) throw new Error("Ошибка авторизации");
        this.user_id = await userReq.json();
        if (this.user_id == this.response.user_id) { window.location.href = `/checkCourseTeacher/${this.courseId}`; return; }
        const assignReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/assignment`, { method: "POST", headers: headers, body: JSON.stringify({ course_id: this.courseId }) });
        if (!assignReq.ok) throw new Error("Ошибка заданий");
        const assignments = await assignReq.json();
        assignments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        this.responseAssignment = assignments.map(el => {
          const now = new Date(); now.setHours(0,0,0,0);
          const d = el.time ? new Date(el.time) : null;
          if(d) d.setHours(0,0,0,0);
          const isOverdue = d && d < now;
          let formattedTime = 'Нет срока';
          if(el.time) { const m = ["", "янв.", "фев.", "мар.", "апр.", "мая", "июн.", "июл.", "авг.", "сен.", "окт.", "ноя.", "дек."]; const p = el.time.split("-"); formattedTime = `${parseInt(p[2])} ${m[parseInt(p[1])]} ${p[0]}`; }
          return { ...el, isOverdue, formattedTime };
        });
        this.responseSubmission = [];
        for (const el of this.responseAssignment) {
          try {
            const subReq = await fetch(`${process.env.VUE_APP_API_BASE_URL}/submission/user/${el.id}`, { method: "POST", headers: headers, body: JSON.stringify({ course_id: this.courseId }) });
            if (subReq.ok) { const data = await subReq.json(); if (data) this.responseSubmission.push(data); }
          } catch (e) { console.error(e); }
        }
      } catch (error) { this.message = "Ошибка загрузки данных курса"; }
    },
    getSubmission(id) { return this.responseSubmission.find(s => s.assignment_id == id) || null; },
    handleFileSelect(e) {
      const f = e.target.files[0];
      if (f) {
        if (!this.validateFileType(f)) { this.fileError = 'Недопустимый формат!'; this.selectedFile = null; this.fileName = ''; e.target.value = ''; setTimeout(() => { this.fileError = ''; }, 4000); return; }
        this.fileError = ''; this.selectedFile = f; this.fileName = f.name; 
      }
    },
    OpenModalAnswer(id) { this.isOpen = true; this.assignmentId = id; this.selectedFile = null; this.fileName = ''; this.fileError = ''; this.userComment = ''; },
    CloseModalAnswer() { this.isOpen = false; this.fileError = ''; },
    async submitAnswer() {
      if (!this.selectedFile) { alert('Выберите файл'); return; }
      if (!this.validateFileType(this.selectedFile)) { this.fileError = 'Недопустимый формат файла!'; return; }
      this.isLoading = true;
      const formData = new FormData();
      formData.append('file', this.selectedFile);
      formData.append('user_comment', this.userComment);
      formData.append('course_id', this.courseId);
      try {
        const headers = new Headers(); headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
        const res = await fetch(`${process.env.VUE_APP_API_BASE_URL}/submission/${this.assignmentId}`, { method: 'POST', headers: headers, body: formData });
        if (res.ok) { 
          this.CloseModalAnswer(); 
          await this.loadCourseData(); 

          this.showApplyNotification = true;
          this.ApplyNotificationText = "Ответ отправлен"
          
          if (this.Timer) clearTimeout(this.Timer);
          this.Timer = setTimeout(() => {
            this.showApplyNotification = false;
          }, 2000);

          //alert('Сдано!'); 
        } else { const r = await res.json(); alert(r.message || 'Ошибка'); }
      } catch (e) { alert('Ошибка сети'); console.error(e); } finally { this.isLoading = false; }
    },
    async DeleteAnswer(el) {
      if(!confirm("Удалить эту работу?")) return;
      const headers = new Headers(); headers.append("Content-Type", "application/json"); headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
      try {
        const req = await fetch(`${process.env.VUE_APP_API_BASE_URL}/submission/${el.id}`, { method: "DELETE", headers: headers, body: JSON.stringify({ course_id: this.courseId }) });
        if(req.ok) { alert("Работа удалена."); await this.loadCourseData(); } else { const err = await req.json().catch(() => ({})); alert("Не удалось удалить: " + (err.message || "Ошибка сети")); }
      } catch(e) { console.error(e); alert("Произошла ошибка при удалении"); }
    }
  }
};
</script>

<style scoped>
/* Твои старые стили... */
.course-page { font-family: 'Inter', sans-serif; background: #F8FAFC; min-height: 100vh; padding: 40px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.main-layout { display: flex; gap: 2%; align-items: flex-start; }
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

/* СТИЛЬ ДЛЯ КНОПКИ ОТПИСКИ */
.btn-leave-course {
  background: #FEF2F2;
  color: #DC2626;
  border: 1px solid #FCA5A5;
  padding: 10px;
  border-radius: 10px;
  text-align: center;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 10px;
}

.btn-leave-course:hover {
  background: #DC2626;
  border-color: #B91C1C;
  transform: translateY(-1px);
}

.file-link-disabled{display: inline-block; background: #F8FAFC; padding: 8px 12px; border-radius: 8px; color: #0966F6; text-decoration: none; font-size: 14px; border: 1px solid #E2E8F0; transition: 0.2s; opacity: .7;}
.content-area { width: 74%; display: flex; flex-direction: column; gap: 20px; }
.section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.section-header h2 { font-size: 28px; color: #0B1C4F; margin: 0; }
.badge-count { background: #0966F6; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 14px; font-weight: 700; }
.assignment-card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #F1F5F9; transition: transform 0.2s; }
.assignment-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; margin-bottom: 15px; }
.assign-title { font-size: 20px; color: #0B1C4F; margin: 0; font-weight: 700; }
.deadline-tag { background: #DCFCE7; color: #16A34A; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.deadline-tag.overdue { background: #FEE2E2; color: #DC2626; }
.assign-desc { color: #475569; line-height: 1.6; margin: 0 0 15px 0; font-size: 15px; }
.assign-meta { margin-bottom: 20px; }
.date-text { font-size: 14px; font-weight: 600; }
.assign-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.btn-link-small { color: #0966F6; text-decoration: none; font-size: 14px; font-weight: 500; padding: 8px 12px; background: #EFF6FF; border-radius: 8px; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
.btn-link-small:hover { background: #DBEAFE; }
.btn-submit { background: #0966F6; color: #fff; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.btn-submit:hover { background: #0759C9; }
.btn-delete-disabled { background: #F1F5F9; color: #94A3B8; border: 1px solid #CBD5E1; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: not-allowed; opacity: 0.7; }
.btn-delete-small { background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.btn-delete-small:hover { background: #FEE2E2; }
.btn-submit-disabled { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: not-allowed; opacity: 0.8; }
.submission-result { margin-top: 25px; background: #F8FAFC; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; }
.sub-header { display: flex; justify-content: space-between; margin-bottom: 15px; }
.sub-header h4 { margin: 0; color: #0B1C4F; font-size: 16px; }
.status-ok { background: #DCFCE7; color: #16A34A; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.sub-body { display: flex; flex-direction: column; gap: 15px; }
.sub-row label { display: block; font-size: 12px; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
.sub-row p { margin: 0; background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #E2E8F0; font-size: 14px; color: #334155; }
.sub-row.teacher p { background: #FFFBEB; border-color: #FCD34D; }
.file-row .file-display { display: flex; align-items: center; gap: 10px; background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #E2E8F0; flex-wrap: wrap; }
.file-icon { font-size: 18px; }
.file-name-text { font-weight: 500; color: #334155; word-break: break-all; }
.no-file-text { color: #94A3B8; font-style: italic; }
.btn-open-file { margin-left: auto; background: #EFF6FF; color: #0966F6; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.2s; white-space: nowrap; }
.btn-open-file:hover { background: #DBEAFE; }
.grade-row { display: flex; align-items: center; gap: 15px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #CBD5E1; }
.grade-circle { background: #EFF6FF; color: #0966F6; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; }
.grade-row.waiting span { color: #F59E0B; font-style: italic; }
.empty-state { text-align: center; padding: 60px; background: #fff; border-radius: 16px; border: 2px dashed #CBD5E1; color: #64748B; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 999; }
.modal-window { background: #fff; width: 100%; max-width: 450px; border-radius: 20px; overflow: hidden; animation: slideIn 0.3s ease; }
@keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-head { padding: 20px 25px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; }
.modal-head h3 { margin: 0; font-size: 20px; color: #0B1C4F; }
.close-x { background: none; border: none; font-size: 24px; cursor: pointer; color: #94A3B8; }
.modal-body { padding: 25px; display: flex; flex-direction: column; gap: 15px; }
.upload-box { border: 2px dashed #CBD5E1; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; background: #F8FAFC; transition: 0.2s; position: relative; }
.upload-box:hover { border-color: #0966F6; background: #EFF6FF; }
.upload-error { border-color: #DC2626 !important; background-color: #FEF2F2 !important; animation: shake 0.3s ease-in-out; }
.upload-icon { font-size: 32px; margin-bottom: 10px; }
.file-name { color: #16A34A; font-weight: 600; margin: 0; }
.upload-hint { color: #64748B; margin: 0; font-size: 14px; }
.upload-subhint { color: #94A3B8; font-size: 11px; margin-top: 5px; margin-bottom: 0; }
.error-text { color: #DC2626; font-size: 12px; font-weight: 600; text-align: left; padding-left: 5px; margin: 0; }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
.modal-textarea { width: 100%; border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px; font-family: inherit; resize: vertical; box-sizing: border-box; background: #F8FAFC; }
.modal-textarea:focus { outline: none; border-color: #0966F6; background: #fff; }
.modal-foot { padding: 20px 25px; background: #F8FAFC; display: flex; justify-content: flex-end; gap: 10px; }
.btn-cancel { background: #fff; border: 1px solid #CBD5E1; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 600; color: #64748B; }
.btn-confirm { background: #0966F6; color: #fff; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 600; }
.btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }
@media (max-width: 768px) { .main-layout { flex-direction: column; } .sidebar, .content-area { width: 100%; } .sidebar-content { position: static; } }

/* Базовый стиль кнопки */
.btn-leave-course {
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
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.btn-leave-course:hover {
  background: #FEE2E2;
  border-color: #EF4444;
}

/* Стиль при активном режиме подтверждения */
.btn-leave-course.is-confirming {
  background: #DC2626;
  color: #fff;
  border-color: #B91C1C;
  animation: pulse-danger 1s infinite;
}

.btn-leave-course.is-confirming:hover {
  background: #B91C1C;
  transform: scale(1.02);
}

/* Анимация пульсации */
@keyframes pulse-danger {
  0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
  100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}

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
</style>