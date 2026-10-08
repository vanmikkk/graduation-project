<template>
  <main>
    <div class="container">
      <div class="main_inner">
        <div class="main_left">
          <h1>{{ message }}</h1>
          <div class="course">
            <div class="img"><img :src="courseImages[response.img_id]" alt=""></div>
            <h3>{{ response.name }}</h3>
          </div>
          <a class="back" :href="'/posts/'+response.id">Перейти к постам</a>
          <a class="back" href="/courses">Назад к курсам</a>
        </div>
        <div class="main_right">
          <div class="assignment" v-for="assignment in responseAssignment" :key="assignment.id">
            <h4>{{ assignment.text }}</h4>
            <p>{{ assignment.description }}</p>
            <div class="links_time">
              <div class="links">
                <a :href="'http://127.0.0.1:8000/storage/' + assignment.file" alt="">Открыть задание</a>
                <a v-if="getSubmission(assignment.id)" href="#" alt="" @click="DeleteAnswer(getSubmission(assignment.id))">Удалить ответ</a>
                <a v-else href="#" alt="" @click="OpenModalAnswer(assignment.id)">Прикрепить ответ</a>
              </div>
              <p class="time">Срок сдачи: <span :style="{color: assignment.color}">{{ assignment.formattedTime }}</span></p>
            </div>
            <div v-for="submission in responseSubmission" :key="submission.id">
              <div v-if="submission.assignment_id == assignment.id && submission != {}" class="submission">
                <div class="line"></div>
                <div class="answer">
                  <div class="answer_block">
                    <h5>Ваш комментарий:</h5>
                    <p>{{ submission.user_comment }}</p>
                  </div>
                  <div class="answer_block">
                    <h5 v-if="submission.teacher_comment != null">Комментарий преподавателя:</h5>
                    <p v-if="submission.teacher_comment != null">{{ submission.teacher_comment }}</p>
                    <div class="mark">
                      <h5>Оценка: </h5>
                      <h6>{{ submission.score == null ? "" : submission.score}}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </main>
    <div v-if="isOpen" class="modal-overlay" @click.self="CloseModalAnswer">
    <div class="modal-content">
      <h3>Сдать задание</h3>
      <div class="file-input-wrapper" @click="$refs.fileInput.click()">
        <span class="file-label">{{ fileName || 'Выбрать файл...' }}</span>
        <input 
          type="file" 
          ref="fileInput" 
          @change="handleFileSelect" 
          hidden 
        />
      </div>
      <textarea 
        v-model="userComment" 
        placeholder="Комментарий..." 
        class="comment-area"
      ></textarea>
      <button @click="submitAnswer" class="submit-btn" :disabled="isLoading">
        {{ isLoading ? 'Отправка...' : 'Прикрепить ответ' }}
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
        userComment: '',
        assignmentId: '',

        user_id: "",

        response: [],
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
        message: ""
      }
    },
    methods: {

      getSubmission(assignmentId) {
        if (!this.responseSubmission) return null;
        const found = this.responseSubmission.find(sub => sub.assignment_id == assignmentId);
        return found || null;
      },

      handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
          this.selectedFile = file;
          this.fileName = file.name;
        }
      },
      OpenModalAnswer(id){
        this.isOpen = true;
        this.assignmentId = id
      },
      CloseModalAnswer(){
        this.isOpen = false;
        this.assignmentId = ""
      },
      async submitAnswer() {
        if (!this.selectedFile) {
          alert('Пожалуйста, выберите файл с решением');
          return;
        }

        this.isLoading = true;

        const formData = new FormData();
        formData.append('file', this.selectedFile);
        formData.append('user_comment', this.userComment);
        formData.append('course_id', this.courseId);

        try {
          const headers = new Headers();
          headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`);
          const response = await fetch(`http://127.0.0.1:8000/api/submission/${this.assignmentId}`, {
            method: 'POST',
            headers: headers,
            body: formData
          });

          const result = await response.json();

          if (response.ok) {
            console.log('Ответ отправлен:', result);
            this.$emit('submitted', result.attachment);
            this.CloseModalAnswer();
            location.reload();
          } else {
            let errorMsg = 'Ошибка при отправке';
            if (result.errors) {
              errorMsg = Object.values(result.errors).flat().join('\n');
            } else if (result.message) {
              errorMsg = result.message;
            } else if (result.error) {
              errorMsg = result.error;
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
      async DeleteAnswer(el){
        try{
          let headers = new Headers()
          headers.append("Content-Type", "application/json")
          headers.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
          let request = await fetch(`http://127.0.0.1:8000/api/submission/${el.id}`, {
            method: "DELETE",
            headers: headers,
            body: JSON.stringify({
              course_id: this.courseId
            })
          })
          if(!request.ok){
            console.log(response)
            alert("Неудалось удалить ответ")
          } else {
            location.reload();
          }

        } catch(error){
          console.log(error);
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
          if(this.user_id == this.response.user_id){
            window.location.href = `/checkCourseTeacher/${this.courseId}`
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

              try{
                let headers3 = new Headers()
                headers3.append("Content-Type", "application/json")
                headers3.append("Authorization", `Bearer ${localStorage.getItem("token")}`)

                let request3 = await fetch(`http://127.0.0.1:8000/api/submission/user/${element.id}`, {
                  method: "POST",
                  headers: headers3,
                  body: JSON.stringify({
                    course_id: this.courseId
                  })
                })
                if(!request3.ok){
                  console.log(request3)
                } else {
                  this.responseSubmission.push(await request3.json());
                  //console.log(this.responseSubmission)
                }
              } catch(error){

              }
              console.log(this.responseSubmission)
            });

            if(this.responseAssignment == ""){
              document.querySelector(".main_right").style.display = "none"
            } else {
              // console.log(this.responseAssignment)
            }
          }
        } catch(error){
          console.log(error);
        }


        // try{
        //   let headers3 = new Headers()
        //   headers3.append("Content-Type", "application/json")
        //   headers3.append("Authorization", `Bearer ${localStorage.getItem("token")}`)
        //   let request3 = await fetch(`http://127.0.0.1:8000/api/submission/user/${}`, {
        //     method: "POST",
        //     headers: headers3,
        //     body: JSON.stringify({
        //       course_id: this.courseId
        //     })
        //   })
        //   if(!request3.ok){
        //     console.log(response3)
        //   } else {
            

        //     if(this.responseAssignment == ""){
        //       document.querySelector(".main_right").style.display = "none"
        //     } else {
        //       // console.log(this.responseAssignment)
        //     }
        //   }
        // }catch(error){

        // }
    }
  }
</script>

<style scoped>
  .assignment,.assignment p,.line{margin-bottom:20px}.mark{display:flex;gap:5px}h6{border:1px solid #0b1c4f;border-radius:5px;width:30px;height:30px;line-height:130%;text-align:center;font-size:20px}.answer_block p{font-size:14px;color:#555}h5{font-size:16px;color:#0b1c4f}h3,h4{font-size:24px;color:#0b1c4f;font-weight:600}.answer{display:flex;justify-content:space-between}.answer_block{width:49%}.line{width:100%;height:1px;background:#0b1c4f21;display:block}.submission{margin-top:20px}.main_inner,.nav_authorized{margin-top:40px;display:flex}.assignment a{border:1px solid #0b1c4f;border-radius:30px;padding:10px 30px}.assignment,.course,.main_right{border-radius:30px}.active{text-decoration:underline}main{margin-bottom:50px}.links_time{display:flex;justify-content:space-between;align-items:center}.links{display:flex;gap:30px}.main_inner{flex-wrap:wrap;justify-content:left;gap:1%;row-gap:20px}.main_left{width:24%;display:flex;flex-direction:column;row-gap:10px}.course,.img,.img img{width:100%}.nav_authorized{gap:30px}.course{display:flex;flex-direction:column;background:#f6f8fb;gap:10px}a{color:#0966f6;text-decoration:none;transition:.1s}a:hover{color:#0b1c4f}h3{padding:0 0 20px 20px}h1{color:#0b1c4f;font-weight:600}.main_right{width:74%;background:#f6f8fb;padding:30px}.assignment{padding:25px;box-shadow:0 0 8px 0 rgba(121,121,121,.1)}

</style>