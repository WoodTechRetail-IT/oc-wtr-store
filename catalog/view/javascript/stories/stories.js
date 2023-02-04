class SlideStories {
  constructor(id) {
    this.slide = document.querySelector(`[data-slide="${id}"]`);
    this.active = 0;
    this.init();
  }


  activeSlide(index) {
    this.active = index;
    this.items.forEach((item) => item.classList.remove('active'));
    this.items[index].classList.add('active');
    this.thumbItems.forEach((item) => item.classList.remove('active'));
    this.thumbItems[index].classList.add('active');
    this.autoSlide();
  }

  prev() {
    if (this.active > 0) {
      this.activeSlide(this.active - 1);
    } else {
      this.activeSlide(this.items.length - 1);
    }

  }

  next() {

    if (this.active < (this.items.length - 1)) {
      this.activeSlide(this.active + 1);
    } else {


      // $('#imagemodal').modal('hide');
      // this.activeSlide(0);
    }

  }

  addNavigation() {
    const nextBtn = this.slide.querySelector('.story-slide-next');
    const prevBtn = this.slide.querySelector('.story-slide-prev');

    nextBtn.addEventListener('click', this.next);
    prevBtn.addEventListener('click', this.prev);

    //arrows
    const nextBtnArrow = this.slide.querySelector('.story-right-arrow');
    const prevBtnArrow = this.slide.querySelector('.story-left-arrow');
    nextBtnArrow.addEventListener('click', this.next);
    prevBtnArrow.addEventListener('click', this.prev);
  }

  addThumbItems() {
    this.items.forEach(() => (this.thumb.innerHTML += `<span></span>`));
    this.thumbItems = Array.from(this.thumb.children);
  }

  autoSlide() {
    clearTimeout(this.timeout);
    this.timeout = setTimeout(this.next, 5000);

  }

  firstInit = true;

  init() {
    this.next = this.next.bind(this);
    this.prev = this.prev.bind(this);
    this.items = this.slide.querySelectorAll('.story-slide-items > img');
    this.thumb = this.slide.querySelector('.story-slide-thumb');
    this.addThumbItems();
    this.activeSlide(0);
    this.addNavigation();
  }
}


