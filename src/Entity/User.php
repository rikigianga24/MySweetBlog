<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;


    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=Blog::class, mappedBy="user")
     */
    private $blogs;

    public function __construct()
    {
        $this->blogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /** Returning a salt is only needed, if you are not using a modern
    * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
    *
    * @see UserInterface
    */
   public function getSalt(): ?string
   {
       return null;
   }

   /** @see UserInterface
   */
  public function eraseCredentials()
  {
      // If you store any temporary, sensitive data on the user, clear it here
      // $this->plainPassword = null;
  }

  public function getRoles(): array
  {
      $roles = $this->roles;
      // guarantee every user at least has ROLE_USER
      $roles[] = 'ROLE_USER';

      return array_unique($roles);
  }

  public function setRoles(array $roles): self
  {
      $this->roles = $roles;

      return $this;
  }

  /**
   * @return Collection|Blog[]
   */
  public function getBlogs(): Collection
  {
      return $this->blogs;
  }

  public function addBlog(Blog $blog): self
  {
      if (!$this->blogs->contains($blog)) {
          $this->blogs[] = $blog;
          $blog->setUser($this);
      }

      return $this;
  }

  public function removeBlog(Blog $blog): self
  {
      if ($this->blogs->removeElement($blog)) {
          // set the owning side to null (unless already changed)
          if ($blog->getUser() === $this) {
              $blog->setUser(null);
          }
      }

      return $this;
  }

  

}
