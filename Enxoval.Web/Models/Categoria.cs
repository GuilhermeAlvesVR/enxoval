using System.ComponentModel.DataAnnotations;

namespace Enxoval.Web.Models;

public class Categoria
{
    public int Id { get; set; }

    [Required, MaxLength(100)]
    public string Nome { get; set; } = "";

    public int Ordem { get; set; }

    public List<Desejo> Desejos { get; set; } = new();
}
