<template>
  <main>
    <div class="container">
      <div class="main_inner">
        <div class="main_left">
          <h1>{{ message }}</h1>
          <div class="course">
            <div class="img"><img :src="courseImages[response.img_id]" alt=""></div>
            <h3>{{ response.name }}</h3>
            <p>Код доступа: {{ response.access_code }}</p>
          </div>
          <a class="back" :href="'/posts/'+response.id">Перейти к постам</a>
          <a class="back" href="/courses">Назад к курсам</a>
        </div>
        <div class="main_right">
          <a href="#" style="margin-bottom: 10px !important; display: block;" @click="OpenModalCreate()">Добавить вложение</a>
          <div class="assignment" v-for="assignment in responseAssignment" :key="assignment.id">
            <h4>{{ assignment.text }}</h4>
            <p>{{ assignment.description }}</p>
            <a class="checkAssignment" :href="'http://127.0.0.1:8000/storage/' + assignment.file" alt="">Посмотреть задание</a>
            <div class="links_time">
              <div class="links">
                <a @click="OpenModalChange(assignment.id)" alt="">Изменить</a>
                <a @click="DeleteAssignment(assignment.id)">Удалить</a>
                <a :href="'/checkSubmissionsTeacherView/' + response.id + '/' +assignment.id" alt="">Посмотреть ответы</a>
              </div>
              <p class="time">Срок сдачи: <span :style="{color: assignment.color}">{{ assignment.formattedTime }}</span></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <div v-if="isOpen" class="modal-overlay" @click.self="CloseModalChange">
    <div class="modal-content">
      <h3>Изменить задание</h3>
      <div class="form-group">
        <input class="file-input-wrapper" type="text" placeholder="Название" v-model="form.name" />
      </div>
      <div class="form-group">
        <input class="file-input-wrapper" type="date"  v-model="form.time" />
      </div>
      <textarea 
        v-model="form.description" 
        placeholder="Комментарий..." 
        class="comment-area"
      ></textarea>
      <div class="file-input-wrapper" @click="$refs.fileInput.click()">
        <span class="file-label">{{ fileName || 'Выбрать файл...' }}</span>
        <input 
          type="file" 
          ref="fileInput" 
          @change="handleFileSelect" 
          hidden 
        />
      </div>
      <button @click="submitAnswer" class="submit-btn" :disabled="isLoading">
        {{ isLoading ? 'Отправка...' : 'Изменить задание' }}
      </button>
    </div>
  </div>

  <div v-if="isOpenCreate" class="modal-overlay" @click.self="CloseModalCreate">
    <div class="modal-content">
      <h3>Добавить задание</h3>
      <div class="form-group">
        <input class="file-input-wrapper" type="text" placeholder="Название" v-model="formCreate.nameCreate" />
      </div>
      <div class="form-group">
        <input class="file-input-wrapper" type="date"  v-model="formCreate.timeCreate" />
      </div>
      <textarea 
        v-model="formCreate.descriptionCreate" 
        placeholder="Комментарий..." 
        class="comment-area"
      ></textarea>
      <div class="file-input-wrapper" @click="$refs.fileInput.click()">
        <span class="file-label">{{ fileNameCreate || 'Выбрать файл...' }}</span>
        <input 
          type="file" 
          ref="fileInput" 
          @change="handleFileSelectCreate" 
          hidden 
        />
      </div>
      <button @click="submitAnswerCreate" class="submit-btn" :disabled="isLoading">
        {{ isLoadingCreate ? 'Отправка...' : 'Добавить задание' }}
      </button>
    </div>
  </div>
</template>

<script>
  export default {
    data(){
      return {
        isOpen: false,
        isLoading: false,
        fileName: '',
        selectedFile: null,
        assignmentId: '',

        isOpenCreate: false,
        isLoadingCreate: false,
        fileNameCreate: '',
        selectedFileCreate: null,
        assignmentId: '',

        isFileChanged: false, 
        isFileChangedCreate: false, 
        form: {
          name: "",
          time: "",
          description: "",
        },

        formCreate: {
          nameCreate: "",
          timeCreate: "",
          descriptionCreate: "",
        },

        user_id: "",

        response: [],
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
        message: ""
      }
    },
    methods: {
      handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
          this.selectedFile = file;
          this.fileName = file.name;
          this.isFileChanged = true;
        }
      },
      handleFileSelectCreate(event) {
        const file = event.target.files[0];
        if (file) {
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
          const fullPath = assignment.file; 
          const fileNameOnly = fullPath.substring(fullPath.lastIndexOf('/') + 1);
          
          this.fileName = `Текущий файл: ${fileNameOnly} (нажмите, чтобы заменить)`;
          this.selectedFile = null;
          this.isFileChanged = false; 
        }
      },

      CloseModalChange() {
        this.isOpen = false;
        this.assignmentId = '';
        this.form = { name: '', time: '', description: '' };
        this.fileName = '';
        this.selectedFile = null;
      },

      OpenModalCreate() {
        this.isOpenCreate = true;
        
        this.selectedFile = null;
        this.isFileChanged = false; 
        
      },

      CloseModalCreate() {
        this.isOpenCreate = false;
        this.assignmentId = '';
        this.formCreate = { name: '', time: '', description: '' };
        this.fileNameCreate = '';
        this.selectedFileCreate = null;
      },

      async submitAnswer() {
        if (!this.form.name) {
          alert('Введите название задания');
          return;
        }

        this.isLoading = true;
        const formData = new FormData();
        formData.append('text', this.form.name);
        formData.append('time', this.form.time);
        formData.append('description', this.form.description);
        formData.append('course_id', this.courseId);
        if (this.selectedFile) {
          formData.append('file', this.selectedFile);
        }
        formData.append('_method', 'PATCH'); 

        try {
          const headers = new Headers();
          headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
          const response = await fetch(`http://127.0.0.1:8000/api/assignment/${this.assignmentId}`, {
            method: 'POST', 
            headers: headers,
            body: formData
          });

          const result = await response.json();

          if (response.ok) {
            alert('Задание обновлено!');
            this.CloseModalChange();
            location.reload();
          } else {
            let errorMsg = 'Ошибка при обновлении';
            if (result.errors) {
              errorMsg = Object.values(result.errors).flat().join('\n');
            } else if (result.message) {
              errorMsg = result.message;
            }
            alert(errorMsg);
          }
        } catch (error) {
          console.error('Ошибка сети:', error);
          alert('Не удалось соединиться с сервером');
        } finally {
          this.isLoading = false;
        }
      },

      async submitAnswerCreate() {
        if (!this.formCreate.nameCreate) {
          alert('Введите название задания');
          return;
        }

        this.isLoadingCreate = true;
        const formData = new FormData();
        formData.append('text', this.formCreate.nameCreate);
        formData.append('time', this.formCreate.timeCreate);
        formData.append('description', this.formCreate.descriptionCreate);
        formData.append('course_id', this.courseId);
        if (this.selectedFileCreate) {
          formData.append('file', this.selectedFileCreate);
        } 

        try {
          const headers = new Headers();
          headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
          const response = await fetch(`http://127.0.0.1:8000/api/assignment/${this.courseId}`, {
            method: 'POST', 
            headers: headers,
            body: formData
          });

          const textResult = await response.text();
          let result;
          try {
            result = textResult ? JSON.parse(textResult) : {};
          } catch (e) {
            console.error("Ответ не JSON:", textResult);
            alert("Ошибка сервера: неверный формат ответа");
            this.isLoadingCreate = false;
            return;
          }

          if (response.ok) {
            alert('Задание успешно создано!');
            this.CloseModalCreate();
            location.reload();
          } else {
            let errorMsg = 'Ошибка при создании';
            if (result.errors) {
              errorMsg = Object.values(result.errors).flat().join('\n');
            } else if (result.message) {
              errorMsg = result.message;
            }
            alert(errorMsg);
          }
        } catch (error) {
          console.error('Ошибка сети:', error);
          alert('Не удалось соединиться с сервером');
        } finally {
          this.isLoadingCreate = false;
        }
      },

      async DeleteAssignment(id){
        let confirmation = confirm("Удалить задание?")
        if(confirmation){
          try{
            let headers = new Headers()
            headers.append("Content-Type", "application/json")
            headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
            let request = await fetch(`http://127.0.0.1:8000/api/assignment/${id}`, {
              method: "DELETE",
              headers: headers,
              body: JSON.stringify({
                course_id: this.courseId
              })
            })
            if(!request.ok){
              alert("Неудалось удалить задание")
            } else {
              location.reload();
            }
  
          } catch(error){
            console.log(error);
          }
        }
      }
    },
    async mounted(){
      try{
        let headers = new Headers()
        headers.append("Content-Type", "application/json")
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
        let request = await fetch("http://127.0.0.1:8000/api/course", {
          method: "POST",
          headers: headers,
          body: JSON.stringify({
            course_id: this.courseId
          })
        })
        if(!request.ok){
          console.log(response)
          alert("Неудалось открыть курс")
        } else {
          this.response = await request.json();
          
        }

      } catch(error){
        document.querySelector(".course").style.display = "none"
        this.message = "У вас нет доступа к этому курсу"
        console.log(error);
      }

      try{
        let headers = new Headers()
        headers.append("Content-Type", "application/json")
        headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
        let request = await fetch("http://127.0.0.1:8000/api/users", {
          method: "POST",
          headers: headers,
        })
        if(!request.ok){
          console.log(response)
          alert("Неудалось открыть курс")
        } else {
          this.user_id = await request.json();
          if(this.user_id != this.response.user_id){
            window.location.href = `/`
          }
        }

      } catch(error){
        document.querySelector(".course").style.display = "none"
        this.message = "У вас нет доступа к этому курсу"
        console.log(error);
      }

      try{
          let headers2 = new Headers()
          headers2.append("Content-Type", "application/json")
          headers2.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
          let request2 = await fetch("http://127.0.0.1:8000/api/assignment", {
            method: "POST",
            headers: headers2,
            body: JSON.stringify({
              course_id: this.courseId
            })
          })
          if(!request2.ok){
            console.log(response2)
            alert("Неудалось открыть вложения")
          } else {
            this.responseAssignment = await request2.json();

            const monthNames = [
              "", "января", "февраля", "марта", "апреля", "мая", "июня",
              "июля", "августа", "сентября", "октября", "ноября", "декабря"
            ];

            const now = new Date();

            this.responseAssignment.forEach(async element => {
              if (!element.time) return;
              const assignmentDate = new Date(element.time);
              now.setHours(0, 0, 0, 0);
              assignmentDate.setHours(0, 0, 0, 0);
              if (assignmentDate < now) {
                element.color = "#B20606";
              } else {
                element.color = "#208C05";
              }

              let parts = element.time.split("-");
              
              let year = parts[0];
              let monthNum = parseInt(parts[1], 10);
              let day = parseInt(parts[2], 10);

              element.formattedTime = `${day} ${monthNames[monthNum]} ${year}г.`;
            });
          }
        } catch(error){
          console.log(error);
        }
    }
  }
</script>

<style scoped>
  .assignment,.line{margin-bottom:20px}.checkAssignment{border:none!important;padding:0!important}.mark{display:flex;gap:5px}h6{border:1px solid #0b1c4f;border-radius:5px;width:30px;height:30px;line-height:130%;text-align:center;font-size:20px}.answer_block p{font-size:14px;color:#555}h5{font-size:16px;color:#0b1c4f}h3,h4{font-size:24px;color:#0b1c4f;font-weight:600}.answer{display:flex;justify-content:space-between}.answer_block{width:49%}.line{width:100%;height:1px;background:#0b1c4f21;display:block}.submission{margin-top:20px}.assignment a{border:1px solid #0b1c4f;border-radius:30px;padding:10px 30px}.assignment,.course,.main_right{border-radius:30px}.assignment p{margin-bottom:0!important}.active{text-decoration:underline}main{margin-bottom:50px}.links_time{display:flex;justify-content:space-between;align-items:center}.links{display:flex;gap:30px;margin-top:10px}.main_inner,.nav_authorized{margin-top:40px;display:flex}.main_inner{flex-wrap:wrap;justify-content:left;gap:1%;row-gap:20px}.main_left{width:24%;display:flex;flex-direction:column;row-gap:10px}.course,.img,.img img{width:100%}.nav_authorized{gap:30px}.course{display:flex;flex-direction:column;background:#f6f8fb;gap:10px}a{color:#0966f6;text-decoration:none;transition:.1s}a:hover{color:#0b1c4f}h3{padding:0 0 0 20px}.course p{color:#0b1c4f;font-weight:600;padding:0 0 20px 20px}h1{color:#0b1c4f;font-weight:600}.main_right{width:74%;background:#f6f8fb;padding:30px}.assignment{padding:25px;box-shadow:0 0 8px 0 rgba(121,121,121,.1)}

</style>